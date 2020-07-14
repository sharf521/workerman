<?php

namespace App\Model;

class ChatUser extends Model
{
    protected $table = 'chat_user';

    public function __construct()
    {
        parent::__construct();
    }

    public function toArray()
    {
        $arr = array(
            'avatar'   => $this->avatar,
            'id'       => $this->id,
            'nickname' => $this->nickname,
            'sign'     => $this->sign
        );
        return $arr;
    }
}