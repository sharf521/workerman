<?php
//前台控制器父类

namespace App\Controller\Home;


use App\Controller\Controller;

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->template = 'default';
    }

    public function index()
    {

    }
}