<?php
namespace App\Model;

use System\Lib\Model as BaseModel;

class Model extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->dbfix=DB_CONFIG_FIX;
    }

    protected function returnSuccess($data = array())
    {
        $data['code'] = '0';
        return $data;
    }

    protected function returnError($msg)
    {
        $data = array(
            'code' => 'fail',
            'msg' => $msg
        );
        return $data;
    }
}