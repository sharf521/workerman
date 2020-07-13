<?php

namespace App\Model;

class User extends Model
{
    protected $table = 'user';

    public function __construct()
    {
        parent::__construct();
    }

    public function toArray()
    {
        $arr = array(
            'username'   => $this->username,
            'qq'         => $this->qq,
            'headimgurl' => $this->headimgurl,
            'email'      => $this->email,
            'nickname'   => $this->nickname
        );
        return $arr;
    }

    public function getHeadImgUrl()
    {
        return $this->headimgurl;
    }
}