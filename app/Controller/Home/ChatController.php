<?php

namespace App\Controller\Home;

use App\Model\AppUser;
use App\Token;
use System\Lib\Request;

class ChatController extends HomeController
{
    public function __construct()
    {
        parent::__construct();
        $this->template = 'default';
    }

    public function index()
    {
        if($this->is_wap){
            $this->template = 'default_wap';
        }
        $this->view('index');
    }

    public function history(Request $request)
    {
        $user_id      = Token::getUid($request->get(2));
        $data['uid']  = $user_id;
        $data['id']   = $request->get('id');
        $data['type'] = $request->get('type');
        $this->view('history', $data);
    }

    public function kefu()
    {
        $this->view('kefu');
    }

    //chat/chatWap/?app_id=10&token={}&to_uid={app user id}&to_im_id={chat user id}
    public function chatWap(Request $request)
    {
        $data['user_id'] = Token::getUid($request->get('token'));
        if ($data['user_id'] == 0) {
            $data['user_id'] = (int)$request->get('id');
        }
        if(empty($data['user_id'])){
            echo 'token error';
            exit;
        }
        $to_uid = (int)$request->get('to_uid');
        $app_id = (int)$request->get('app_id');
        if (empty($app_id)) {
            $app_id = 10;
        }
        $data['app_id'] = $app_id;
        if($to_uid!=0){
            $toUser         = (new AppUser())->getUserOrCreate($to_uid, 10);
            $data['toUser'] = array(
                'id'       => $toUser['id'],
                'username' => $toUser['nickname'],
                'avatar'   => $toUser['avatar']
            );
        }else{
            $id = (int)$request->get('to_im_id');
            $toUser=(new AppUser())->find($id);
            $data['toUser'] = array(
                'id'       => $toUser->id,
                'username' => $toUser->nickname,
                'avatar'   => $toUser->avatar
            );
        }

        $this->template = 'default_wap';
        $this->view('chatWap', $data);
    }

    private function socketSend($data = array())
    {
        // 建立连接
        //$client = stream_socket_client('tcp://127.0.0.1:7273');
        $client = stream_socket_client('tcp://121.41.30.46:7273');
        if (!$client) exit("can not connect");
        // 模拟超级用户，以文本协议发送数据，协议末尾有换行符（发送的数据中最好有能识别超级用户的字段），
        //这样在Event.php中的onMessage方法中便能收到这个数据，然后做相应的处理即可
        fwrite($client, json_encode($data) . "\n");
    }

    public function socket()
    {
        //test
        $data = array(
            'type'     => 'addTimerCurl',
            'url'       => 'http://app.5-58.com',
            'minute'=>0.1
        );
        $this->socketSend($data);
        echo json_encode(array('code' => 0));
    }
}