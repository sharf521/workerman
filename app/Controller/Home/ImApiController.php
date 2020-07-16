<?php

namespace App\Controller\Home;

use App\Config;
use App\Model\AppUser;
use App\Model\ChatLog;
use App\Token;
use System\Lib\Request;

class ImApiController extends HomeController
{
    private $redis;

    public function __construct()
    {
        parent::__construct();
        $this->template = 'default';
        $this->redis    = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public function initUser(Request $request)
    {
        $user_id  = $request->post('user_id');
        $app_id   = (int)$request->post('app_id');
        $avatar   = $request->post('avatar');
        $nickname = $request->post('nickname');
        if (empty($avatar)) {
            $avatar   = 'http://lorempixel.com/38/38/?' . $user_id;
        }
        if (empty($nickname)) {
            $nickname = 'user' . $user_id;
        }
        $user           = (new AppUser())->where("user_id='{$user_id}' and app_id={$app_id}")->first();
        $user->avatar   = $avatar;
        $user->nickname = $nickname;
        if (!$user->is_exist) {
            $user->user_id = $user_id;
            $user->app_id  = $app_id;
            $user->sign    = '';
            $user->openid  = $user->createOpenId($user_id, $app_id);
            $id            = $user->save(true);
        } else {
            $user->save();
            $id = $user->id;
        }
        $return = array(
            'code' => 0,
            'ws'   => Config::$ws_url,
            'user' => array(
                'id'       => $id,
                'avatar'   => $user->avatar,
                'nickname' => $user->nickname,
                'sign'     => $user->sign,
                'token'    => Token::createToken($id, 1)
            )
        );
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

    public function init(AppUser $user, Request $request)
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
    public function getList(AppUser $user, Request $request)
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
            "sign"     => $user->sign,
            "avatar"   => $user->avatar,
            "status"   => "online"
        );
        //好友
        $friendGroup = array(
            "groupname" => "在线列表",
            "id"        => 0,
            "list"      => array()
        );
        $arr_online  = array();
        $list        = $this->redis->hGetAll('group:101');
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
            "id"        => "101",
            "avatar"    => "http://tp2.sinaimg.cn/2211874245/180/40050524279/0"
        );
        $array['data']['group'][] = $group;
        $json                     = json_encode($array);
        echo $json;
    }

    ////查看群员接口
    public function getGroupMembers(AppUser $user, Request $request)
    {
        $id                     = $request->get('id');
        $uid                = (int)$request->get('uid');
        $user                   = $user->find($uid);
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
        $list = $this->redis->hGetAll('group:101');
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
        $data             = $_POST['data'];
        $chatLog->type    = $data['to']['type'];
        $chatLog->mine_id = $data['mine']['id'];
        $chatLog->content = $data['mine']['content'];
        $chatLog->to_id   = $data['to']['id'];
        $chatLog->save();
    }

    public function changSign(Request $request)
    {
        $uid=$request->post('uid');
        $sign=$request->post('sign');
        $user=(new AppUser())->find($uid);
        var_dump($user);
        if($user->is_exist){
            $user->sign=$sign;
            $user->save();
        }
        echo 'ok';
    }

    public function getOffLineMsg(ChatLog $chatLog,Request $request)
    {
        $uid      = $request->post('uid');
        //"(type='friend' and to_id='{$uid}') or (type='group' and to_id='101' and mine_id!={$uid})"
        $result = $chatLog->where("(type='friend' and to_id='{$uid}' and is_send=0)")->orderBy('id desc')->limit('0,99')->get();
        krsort($result);
        $list=array();
        foreach ($result as $row){
            if($row instanceof ChatLog){
                $user = $row->AppUser();
                $arr=array(
                    'username'   => $user->nickname,
                    'avatar'     => $user->avatar,
                    'id'        => $row->type == 'friend' ? $row->mine_id : $row->to_id,//消息的来源ID（如果是私聊，则是用户id，如果是群聊，则是群组id）
                    'type'      => $row->type,
                    'content'    => $row->content,
                    'fromid'    => $row->type == 'friend' ? $row->mine_id : $row->to_id,//消息的发送者id（比如群组中的某个消息发送者）
                    'mine'      => false, //是否我发送的消息，如果为true，则会显示在右方
                    'cid'       => 0,//消息id，可不传。除非你要对消息进行一些操作（如撤回）
                    'timestamp' => time() * 1000
                );
                $list[]=$arr;
                $row->is_send=1;
                $row->save();
            }
        }
        $array = array(
            'code' => 0,
            'msg'  => '',
            'data' => $list
        );
        echo json_encode($array);
    }

    public function history(ChatLog $chatLog, Request $request)
    {
        $id      = $request->get('id');
        $type    = $request->get('type');
        $user_id = (int)$request->get(2);
        if ($type == 'group') {
            $result = $chatLog->where("type='{$type}' and to_id='{$id}'")->orderBy('id desc')->pager($_GET['page'], 10);
        } else {
            $id     = (int)$id;
            $where  = "type='{$type}' and ((mine_id='{$user_id}' and to_id='{$id}')||(mine_id='{$id}' and to_id='{$user_id}'))";
            $result = $chatLog->where($where)->orderBy('id desc')->pager($_GET['page'], 10);
        }
        $arr_arr = array();
        krsort($result['list']);
        foreach ($result['list'] as $row) {
            if($row instanceof ChatLog){
                $user = $row->AppUser();
                $arr  = array(
                    'id'         => $row->mine_id,
                    'username'   => $user->nickname,
                    'avatar'     => $user->avatar,
                    'type'       => $row->type,
                    'content'    => $row->content,
                    'created_at' => $row->created_at
                );
                array_push($arr_arr, $arr);
            }
        }
        $data['uid']   = $user_id;
        $data['data']  = json_encode($arr_arr);
        $data['page']  = $result['page'];
        $data['total'] = $result['total'];
        $this->view('history', $data);
    }

}