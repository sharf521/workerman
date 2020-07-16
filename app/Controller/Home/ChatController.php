<?php

namespace App\Controller\Home;

class ChatController extends HomeController
{
    public function __construct()
    {
        parent::__construct();
        if($this->is_wap){
            $this->template = 'default_wap';
        }else{
            $this->template = 'default';
        }
    }

    public function index()
    {
        $this->view('index');
    }

    public function kefu()
    {
        $this->view('kefu');
    }
}