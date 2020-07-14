<?php

namespace App\Controller\Home;

use App\Model\ChatLog;
use App\Model\ChatUser;
use App\Model\User;
use App\Token;
use System\Lib\Request;

class ChatController extends HomeController
{
    private $redis;

    public function __construct()
    {
        parent::__construct();
        if($this->is_wap){
            $this->template = 'default_wap';
        }else{
            $this->template = 'default';
        }
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public function index()
    {
        $this->view('index');
    }

    public function initUser(Request $request)
    {
        $id   = (int)$request->post('id');
        $user = (new ChatUser())->find($id);
        if (!$user->is_exist) {
            $user->id = $id;
        }
        $user->avatar   = $request->post('avatar');
        $user->sign     = $request->post('sign');
        $user->nickname = $request->post('nickname');
        $user->save();
        $return = array(
            'code' => 0,
            'user' => array(
                'id'    => $user->id,
                'token' => Token::createToken($user->id, 1)
            )
        );
        echo json_encode($return);
    }

    public function getUserInfo(Request $request)
    {
        $uid  = (int)$request->get('uid');
        $user = (new User())->find($uid);
        if ($user->is_exist) {
            $return = array(
                'code' => 0,
                'user' => array(
                    'id'       => $user->id,
                    'avatar'   => $user->headimgurl,
                    'sign'     => '',
                    'username' => $user->nickname,
                    'token'    => Token::createToken($user->id, 1)
                )
            );
        } else {
            $return = array(
                'code' => 0,
                'msg'  => 'not find user'
            );
        }
        echo json_encode($return);
    }

    private function socketSend($data = array())
    {
        // 建立连接
        $client = stream_socket_client('tcp://127.0.0.1:7273');
        if (!$client) exit("can not connect");
        // 模拟超级用户，以文本协议发送数据，协议末尾有换行符（发送的数据中最好有能识别超级用户的字段），
        //这样在Event.php中的onMessage方法中便能收到这个数据，然后做相应的处理即可
        fwrite($client, json_encode($data) . "\n");
    }

    public function init(User $user, Request $request)
    {
        $id   = $request->id;
        $user = $user->findOrFail($id);
        $data = array(
            'type'     => 'init',
            'id'       => $id,
            'username' => $user->username,
            'avatar'   => $user->headimgurl,
            'sign'     => $user->sign
        );
        $this->socketSend($data);
        echo json_encode(array('code' => 0));
    }

//init
    public function getList(User $user, Request $request)
    {
        $user_id               = (int)$request->get('uid');
        $user                  = $user->find($user_id);
        $array                 = array(
            'code' => 0,
            'msg'  => '',
            'data' => array()
        );
        $array['data']['mine'] = array(
            "username" => $user->nickname,
            "id"       => $user->id,
            "sign"     => 'hello today!',
            "avatar"   => $user->headimgurl,
            "status"   => "online"
        );
        //好友
        $friendGroup = array(
            "groupname" => "在线列表",
            "id"        => 0,
            "list"      => array()
        );
        $arr_online  = array();
        $list        = $this->redis->hGetAll('group101');
        foreach ($list as $key => $item) {
            if ($key != $user_id) {
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
        $friendGroup['list'] = $arr_online;
        /*        $users=$user->where('id!=?')->bindValues($user_id)->get();
                $arr_online=array();
                $arr_hide=array();
                foreach ($users as $i=>$item){
                    $u=array(
                        "username" => $item->nickname,
                        "id" => $item->id,
                        "sign" => $item->sign,
                        "avatar" =>$item->headimgurl,
                        "status"=>"hide"
                    );
                    if (in_array($item->id, $redis->hKeys('group101'))) {
                        $u['status'] = 'online';
                        array_push($arr_online,$u);
                    }else{
                        array_push($arr_hide,$u);
                    }
                }
                $friendGroup['list']=$arr_online+$arr_hide;*/
        $array['data']['friend'][] = $friendGroup;

        //群组
        $group                    = array(
            "groupname" => "在线群",
            "id"        => "group101",
            "avatar"    => "http://tp2.sinaimg.cn/2211874245/180/40050524279/0"
        );
        $array['data']['group'][] = $group;
        $json                     = json_encode($array);
        echo $json;
    }

    ////查看群员接口
    public function getGroupMembers(User $user, Request $request)
    {
        $id                     = $request->get('id');
        $user_id                = (int)$request->get('uid');
        $user                   = $user->find($user_id);
        $array                  = array(
            'code' => 0,
            'msg'  => '',
            'data' => array()
        );
        $array['data']['owner'] = array(
            "username" => $user->nickname,
            "id"       => $user->id,
            "sign"     => $user->sign,
            "avatar"   => $user->headimgurl
        );
        $array['data']['list']  = array();
        /*        $users=$user->where('id!=?')->bindValues($user_id)->get();
                foreach ($users as $i => $item) {
                    $u = array(
                        "username" => $item->nickname,
                        "id"       => $item->id,
                        "sign"     => $item->sign,
                        "avatar"   => $item->headimgurl
                    );
                    array_push($array['data']['list'], $u);
                }*/
        $list = $this->redis->hGetAll('group101');
        foreach ($list as $key => $item) {
            $item                    = unserialize($item);
            $u                       = array(
                "username" => $item['username'],
                "id"       => $item['id'],
                "sign"     => $item['sign'],
                "avatar"   => $item['avatar'],
                "status"   => "online"
            );
            $array['data']['list'][] = $u;
        }

        $json = json_encode($array);
        echo $json;
    }

    //保存发送的消息
    public function post_message(ChatLog $chatLog)
    {
        $data                   = $_POST['data'];
        $chatLog->type          = $data['to']['type'];
        $chatLog->mine_id       = $data['mine']['id'];
        $chatLog->mine_username = $data['mine']['username'];
        $chatLog->mine_avatar   = $data['mine']['avatar'];
        $chatLog->content       = $data['mine']['content'];
        $chatLog->to_id         = $data['to']['id'];
        if ($chatLog->type == 'group') {
            $chatLog->to_id       = str_replace('group', '', $chatLog->to_id);
            $chatLog->to_username = $data['to']['groupname'];
        } else {
            $chatLog->to_username = $data['to']['username'];
        }
        $chatLog->to_avatar = $data['to']['avatar'];
        $chatLog->save();
    }

    public function history(ChatLog $chatLog, Request $request)
    {
        $id      = $request->get('id');
        $type    = $request->get('type');
        $user_id = (int)$request->get(2);
        if ($type == 'group') {
            $id     = str_replace('group', '', $id);
            $result = $chatLog->where("type='{$type}' and to_id='{$id}'")->orderBy('id desc')->pager($_GET['page'], 10);
        } else {
            $id     = (int)$id;
            $where  = "type='{$type}' and ((mine_id='{$user_id}' and to_id='{$id}')||(mine_id='{$id}' and to_id='{$user_id}'))";
            $result = $chatLog->where($where)->orderBy('id desc')->pager($_GET['page'], 10);
        }
        $arr_arr = array();
        krsort($result['list']);
        foreach ($result['list'] as $row) {
            $arr = array(
                'id'         => $row->mine_id,
                'username'   => $row->mine_username,
                'avatar'     => $row->mine_avatar,
                'type'       => $row->type,
                'content'    => $row->content,
                'created_at' => $row->created_at
            );
            array_push($arr_arr, $arr);
        }
        $data['uid']  = $user_id;
        $data['data'] = json_encode($arr_arr);
        $data['page'] = $result['page'];
        $data['total']=$result['total'];
        $this->view('history', $data);
    }

}