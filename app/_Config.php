<?php

namespace App;

class _Config
{
    // 数据库实例1
    public static $db1 = array(
        'default'  => true,
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'dbname'   => 'chat',
        'charset'  => 'utf8',
        'dbfix'    => ''
    );

    public static $ws_url='192.168.0.25:7272';
}