<?php

//控制器的父类

namespace App\Controller;

use App\Helper;
use System\Lib\Controller as BaseController;

class Controller extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->dbfix       = DB_CONFIG_FIX;
        $this->user_id     = session('user_id');
        $this->username    = session('username');
        $this->user_typeid = session('usertype');
        $this->is_wap      = Helper::is_mobile_request();
        $agent             = addslashes($_SERVER['HTTP_USER_AGENT']);
        if (strpos($agent, 'MicroMessenger') === false && strpos($agent, 'Windows Phone') === false) {
            $this->is_inWeChat = false;
            //die('Sorry！非微信浏览器不能访问');
        } else {
            $this->is_inWeChat = true;
        }

    }
}