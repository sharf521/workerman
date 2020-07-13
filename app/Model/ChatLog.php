<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/25
 * Time: 16:21
 */

namespace App\Model;


class ChatLog extends Model
{
    protected $table='chat_log';
    public function __construct()
    {
        parent::__construct();
    }
}