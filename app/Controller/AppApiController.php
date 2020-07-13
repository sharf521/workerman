<?php
namespace App\Controller;

use System\Lib\Controller as BaseController;

class AppApiController extends BaseController
{
    public function __construct()
    {
        // 指定允许其他域名访问
        $origin=$_SERVER['HTTP_ORIGIN'];// http://localhost:8081
        header("Access-Control-Allow-Origin:{$origin}");
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers:x-requested-with,content-type,Authorization");
        parent::__construct();
        $this->is_wap = true;
    }

    public function error()
    {
        echo 'not find page';
    }

    protected function returnSuccess($data = array())
    {
        $return['return_data'] = $data;
        $return['return_code'] = 'success';
        echo json_encode($return);
        exit;
    }

    protected function returnError($msg)
    {
        $data = array(
            'return_code' => 'fail',
            'return_msg' => $msg
        );
        echo json_encode($data);
        exit;
    }
}