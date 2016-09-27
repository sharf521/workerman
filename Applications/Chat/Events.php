<?php
use \GatewayWorker\Lib\Gateway;
use MyPhp\Lib\DB;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    private static $redis;
    public static function onWorkerStart(Worker $businessWorker)
    {
        self::$redis = new \Redis();
        $redis=self::$redis;
        $redis->pconnect('127.0.0.1', 6379);
        $redis->hSet('chat_room:1', 'id1', json_encode(array('id'=>1,'name'=>2)));
        $redis->hSet('chat_room:1', 'id2', json_encode(array('id'=>2,'name'=>22)));
        $redis->hSet('chat_room:1', 'id3', json_encode(array('id'=>3,'name'=>33)));
// Get the stored data and print it
        $list=$redis->hGetAll('chat_room:1');
        foreach ($list as $k=>$v){
            echo $v."\r\n";
        }
        
    }
    
    /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $data) {       
       //DB::instance(\MyPhp\Config::$db);
       //$user=new \MyPhp\Model\User();

       $message = json_decode($data, true);
       $message_type = $message['type'];
//       if($message_type!='ping'){
           echo "\r\n".$data."\r\n";
//       }
       switch($message_type) {
           case 'init':
               $uid = $message['id'];
               // 设置session
               $_SESSION = array(
                   'username' => $message['username'],
                   'avatar'   => $message['avatar'],
                   'id'       => $uid,
                   'sign'     => $message['sign']
               );
               // 将当前链接与uid绑定
               Gateway::bindUid($client_id, $uid);

               //更新用户信息
               /*$user=$user->find($uid);
               $user->id=$uid;
               $user->username=$message['username'];
               $user->avatar=$message['avatar'];
               $user->save();*/

               // 通知当前客户端初始化
               $init_message = array(
                   'message_type' => 'init',
                   'id'           => $uid,
               );
               Gateway::sendToClient($client_id, json_encode($init_message));
               // 通知所有客户端添加一个好友
               $reg_message = array('message_type'=>'addList', 'data'=>array(
                   'type'     => 'friend',
                   'username' => $message['username'],
                   'avatar'   => $message['avatar'],
                   'id'       => $uid,
                   'sign'     => $message['sign'],
                   'groupid'  => 1
               ));
               Gateway::sendToAll(json_encode($reg_message), null, $client_id);
               // 让当前客户端加入群组101
               Gateway::joinGroup($client_id, 101);
               return;
           case 'chatMessage':
               // 聊天消息
               $type = $message['data']['to']['type'];
               $to_id = $message['data']['to']['id'];
               $uid = $_SESSION['id'];
               $chat_message = array(
                    'message_type' => 'chatMessage',
                    'data' => array(
                        'username' => $_SESSION['username'],
                        'avatar'   => $_SESSION['avatar'],
                        'id'       => $type === 'friend' ? $uid : $to_id,
                        'type'     => $type,
                        'content'  => htmlspecialchars($message['data']['mine']['content']),
                        'timestamp'=> time()*1000,
                    )
               );
               echo $to_id;
               echo json_encode($chat_message);
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
                   'id'           => $_SESSION['id'],
               );
               $_SESSION['online'] = $message_type;
               Gateway::sendToAll(json_encode($status_message));
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
    */
   public static function onClose($client_id) {
       $logout_message = array(
           'message_type' => 'logout',
           'id'           => $_SESSION['id']
       );
       Gateway::sendToAll(json_encode($logout_message));
   }
}