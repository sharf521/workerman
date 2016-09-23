<?php

namespace MyPhp;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/23
 * Time: 16:32
 */
class Config
{
    // 数据库实例1
    public static $db = array(
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'user'    => 'print',
        'password' => 'print_123',
        'dbname'  => 'print',
        'charset'    => 'utf8',
        'dbfix' => 'im_'
    );
}