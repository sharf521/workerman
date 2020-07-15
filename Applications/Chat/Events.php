<?php

use \GatewayWorker\Lib\Gateway;

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
        $id=\App\Token::getUid($data['get']['token']);
        if(!$id>0){
            Gateway::closeClient($client_id);
        }
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $data)
    {
        //DB::instance(\MyPhp\Config::$db);
        //$user=new \MyPhp\Model\User();

        $message      = json_decode($data, true);
        $message_type = $message['type'];
//       if($message_type!='ping'){
        echo "\r\n" . $data . "\r\n";
//       }
        switch ($message_type) {
            case 'init':
                $uid = $message['id'];
                if(empty($uid) || $uid!=\App\Token::getUid($message['token'])){
                    return;
                }
                // 设置session
                $_SESSION['user'] = array(
                    'username' => $message['username'],
                    'avatar'   => $message['avatar'],
                    'id'       => $uid,
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
                Gateway::sendToAll(json_encode($reg_message), null, $client_id);
                // 让当前客户端加入群组101
                Gateway::joinGroup($client_id, 'group101');
                self::$redis->hSet('group101', $uid, serialize($_SESSION['user']));

                // redis同步在线终端
                $uids=Gateway::getUidListByGroup('group101');
                $list=self::$redis->hGetAll('group101');
                $arr_online=[];
                foreach ($list as $key=>$item){
                    if(!array_key_exists($key,$uids)){
                        self::$redis->hDel('group101', $key);
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
                // 通知当前客户端初始化
                $init_message = array(
                    'message_type' => 'init',
                    'id'           => $uid,
                    'online_list'  => $arr_online
                );
                Gateway::sendToClient($client_id, json_encode($init_message));

                return;
            case 'chatMessage':
                // 聊天消息
                $type  = $message['data']['to']['type'];
                $from_id=$message['data']['mine']['id'];
                $to_id = $message['data']['to']['id'];
                $chat_message = array(
                    'message_type' => 'chatMessage',
                    'data'         => array(
                        'username'  => $message['data']['mine']['username'],
                        'avatar'    => $message['data']['mine']['avatar'],
                        'id'        => $type == 'friend' ? $from_id : $to_id,//消息的来源ID（如果是私聊，则是用户id，如果是群聊，则是群组id）
                        'type'      => $type,
                        'content'   => htmlspecialchars($message['data']['mine']['content']),
                        'fromid'    => $message['data']['mine']['id'],//消息的发送者id（比如群组中的某个消息发送者）
                        'mine'      => false, //是否我发送的消息，如果为true，则会显示在右方
                        'cid'       => 0,//消息id，可不传。除非你要对消息进行一些操作（如撤回）
                        'timestamp' => time() * 1000
                    )
                );
                switch ($type) {
                    // 私聊
                    case 'friend':
                        return Gateway::sendToUid($to_id, json_encode($chat_message));
                    // 群聊
                    case 'group':
                        return Gateway::sendToGroup($to_id, json_encode($chat_message), $client_id);
                }
                return;
            case 'hide':
            case 'online':
                $status_message = array(
                    'message_type' => $message_type,
                    'id'           => $_SESSION['user']['id'],
                );
                Gateway::sendToAll(json_encode($status_message));
                return;
            case 'ping':
                return;
            default:
                echo "unknown message $data";
        }
        //DB::close(\MyPhp\Config::$db);
    }

    public static function  getList()
    {
        $list=self::$redis->hGetAll('group101');
        echo count($list)."\r\n";
    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {
        $uid            = $_SESSION['user']['id'];
        $logout_message = array(
            'message_type' => 'logout',
            'id'           => $uid
        );
        Gateway::sendToAll(json_encode($logout_message));
        self::$redis->hDel('group101', $uid);
    }
}