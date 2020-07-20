<?php

namespace App\Controller\Home;

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

    public function chatWap()
    {
        $this->template = 'default_wap';
        $this->view('kefu');
    }
}