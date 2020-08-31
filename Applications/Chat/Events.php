<?php

use \GatewayWorker\Lib\Gateway;

define('MyPHP_KEY', 'kee__ewk__ss__sk');
define('ROOT', __DIR__ . '/../..');
define('DB_CONFIG', \App\Config::$db1);
define('DB_CONFIG_FIX', \App\Config::$db1['dbfix']);

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 * @property Redis $redis
 */
class Events
{
    private static $redis;
    public static $user = array();

    public static function onWorkerStart($businessWorker)
    {
        self::$redis = new \Redis();
        self::$redis->pconnect('127.0.0.1', 6379);
    }

    public static function onWebSocketConnect($client_id, $data)
    {
        //var_export($data);
        if (!isset($data['get']['token'])) {
            Gateway::closeClient($client_id);
        }
        $id = \App\Token::getUid($data['get']['token']);
        if (!$id > 0) {
            Gateway::closeClient($client_id);
        }
    }

    /**
     * 当客户端发来消息时触发
     * @param $client_id
     * @param $data
     * @throws Exception
     */
    public static function onMessage($client_id, $data)
    {
        $message      = json_decode($data, true);
        $message_type = $message['type'];
        if ($message_type != 'ping') {
            echo "\r\n" . $data . "\r\n";
        }
        switch ($message_type) {
            case 'init':
                $uid        = $message['id'];
                if (empty($uid) || $uid != \App\Token::getUid($message['token'])) {
                    return;
                }
                $app_id=(int)$message['app_id'];
                if(empty($app_id)){
                    $app_id=10;
                }
                $_SESSION['user'] = array(
                    'username' => $message['username'],
                    'avatar'   => $message['avatar'],
                    'id'       => $uid,
                    'app_id'   => $app_id,
                    'sign'     => $message['sign']
                );

                // 将当前链接与uid绑定
                Gateway::bindUid($client_id, $uid);

                // 通知所有客户端添加一个好友
                $reg_message = array('message_type' => 'addList', 'data' => array(
                    'type'     => 'friend',
                    'username' => $message['username'],
                    'avatar'   => $message['avatar'],
                    'id'       => $uid,
                    'sign'     => $message['sign'],
                    //'groupid'  => 0//接受端再赋值要添加的组
                ));
                //Gateway::sendToAll(json_encode($reg_message), null, $client_id);
                Gateway::sendToGroup("group:{$app_id}", json_encode($reg_message), $client_id);
                // 让当前客户端加入群组
                Gateway::joinGroup($client_id, "group:{$app_id}");//在线
                self::$redis->hSet("group:{$app_id}", $uid, serialize($_SESSION['user']));
                if ($message['from_dev'] == 'app') {
                    return;
                }

                if (!empty($message['serviceIds'])) {
                    //客服ids
                    $arr_online = [];
                    foreach ($message['serviceIds'] as $uid) {
                        if (Gateway::isUidOnline($uid) == 1) {
                            $arr_online[] = $uid;
                        }
                    }
                } else {
                    // redis同步在线终端
                    $uids       = Gateway::getUidListByGroup("group:{$app_id}");
                    $list       = self::$redis->hGetAll("group:{$app_id}");
                    $arr_online = [];
                    foreach ($list as $key => $item) {
                        if (!array_key_exists($key, $uids)) {
                            self::$redis->hDel("group:0", $key);
                            continue;
                        }
                        if ($key != $uid) {
                            $item         = unserialize($item);
                            $u            = array(
                                "username" => $item['username'],
                                "id"       => $item['id'],
                                "sign"     => $item['sign'],
                                "avatar"   => $item['avatar'],
                                "status"   => "online"
                            );
                            $arr_online[] = $u;
                        }
                    }
                }
                // 通知当前客户端初始化
                $init_message = array(
                    'message_type' => 'init',
                    'id'           => $uid,
                    'online_list'  => $arr_online
                );
                Gateway::sendToClient($client_id, json_encode($init_message));
                return;
            case 'initLuckyBag':
                $uid = \App\Token::getUid($message['token']);
                if ($uid>0) {
                    // 将当前链接与uid绑定
                    Gateway::bindUid($client_id, $uid);
                }
                return;
            case 'joinLuckyBag':
                $group_id   = $message['bag_id'];//分组id
                $uid        = $message['uid'];
                $cidArr=Gateway::getClientIdByUid($uid);
                foreach ($cidArr as $client_id){
                    Gateway::joinGroup($client_id, "group:bag:{$group_id}");
                }
                return;
            case 'chatMessage':
                // 聊天消息
                $type         = $message['data']['to']['type'];
                $from_id      = $message['data']['mine']['id'];
                $to_id        = $message['data']['to']['id'];
                $content      = htmlspecialchars($message['data']['mine']['content']);
                $content_duration=(int)$message['data']['mine']['content_duration'];
                $chat_message = array(
                    'message_type' => 'chatMessage',
                    'data'         => array(
                        'username'        => $message['data']['mine']['username'],
                        'avatar'          => $message['data']['mine']['avatar'],
                        'id'              => $type == 'friend' ? $from_id : $to_id,//消息的来源ID（如果是私聊，则是用户id，如果是群聊，则是群组id）
                        'type'            => $type,
                        'content'         => $content,
                        'content_duration' => $content_duration,
                        'fromid'          => $message['data']['mine']['id'],//消息的发送者id（比如群组中的某个消息发送者）
                        'mine'            => false, //是否我发送的消息，如果为true，则会显示在右方
                        'cid'             => 0,//消息id，可不传。除非你要对消息进行一些操作（如撤回）
                        'timestamp'       => time() * 1000
                    )
                );

                $chatLog          = (new \App\Model\ChatLog());
                $chatLog->type    = $type;
                $chatLog->mine_id = $from_id;
                $chatLog->content = $content;
                $chatLog->content_duration=$content_duration;
                $chatLog->to_id   = $to_id;
                switch ($type) {
                    // 私聊
                    case 'friend':
                        // 如果不在线就先存起来
                        if (!Gateway::isUidOnline($to_id)) {
                            $chatLog->is_send = 0;
                        } else {
                            Gateway::sendToUid($to_id, json_encode($chat_message));
                            $chatLog->is_send = 1;
                        }
                        break;
                    // 群聊
                    case 'group':
                        Gateway::sendToGroup("group:{$to_id}", json_encode($chat_message), $client_id);
                        break;
                }
                $chatLog->save();
                return;
            case 'hide':
            case 'online':
                $status_message = array(
                    'message_type' => $message_type,
                    'id'           => $_SESSION['user']['id'],
                );
                Gateway::sendToAll(json_encode($status_message));
                return;
            case 'addTimerCurl':
                \Workerman\Lib\Timer::add(60 * $message['minute'], function ($url, $about_type, $about_id) {
                    echo "timer {$url}\n";
                    self::log('workerman', "timer {$url}\n");
                    echo self::curl_url($url);
                    if ($about_type == 'openLuckyBag') {
                        $_message = array(
                            'message_type' => 'openLuckyBag',
                            'id'           => $about_id
                        );
                        Gateway::sendToGroup("group:bag:{$about_id}",json_encode($_message));
                    }
                }, array($message['url'], $message['about_type'], $message['about_id']), false);
                return;
            case 'ping':
                return;
            default:
                echo "unknown message $data";
        }
        //DB::close(\MyPhp\Config::$db);
    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     * @throws Exception
     */
    public static function onClose($client_id)
    {
        $uid            = $_SESSION['user']['id'];
        $app_id         = $_SESSION['user']['app_id'];
        $logout_message = array(
            'message_type' => 'logout',
            'id'           => $uid
        );
        $c_list         = Gateway::getClientIdByUid($uid);
        if (empty($c_list)) {
            //uid 的所有终端都下线
            Gateway::sendToAll(json_encode($logout_message));
            self::$redis->hDel("group:{$app_id}", $uid);
        }
    }

    public static function curl_url($url, $data = array())
    {
        $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if ($data) {
            if (is_array($data)) {
                curl_setopt($ch, CURLOPT_POST, 1);
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data))
                );
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if ($ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function log($name = 'error', $data)
    {
        $path = ROOT . "/public/data/logs/";
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $myfile = fopen($path . $name . '_' . date('Ym') . ".txt", "a+");
        if (is_array($data)) {
            $data = json_encode($data);
        }
        fwrite($myfile, '【' . date('Y-m-d H:i:s') . '】' . "\t" . $data . "\r\n");
        fclose($myfile);
    }
}