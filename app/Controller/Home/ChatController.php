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

    //chat/chatWap/?app_id=10&token={}&to_uid=3
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
        $toUser         = (new AppUser())->getUserOrCreate($to_uid, 10);
        $data['toUser'] = array(
            'id'       => $toUser['id'],
            'username' => $toUser['nickname'],
            'avatar'   => $toUser['avatar']
        );
        $this->template = 'default_wap';
        $this->view('chatWap', $data);
    }
}