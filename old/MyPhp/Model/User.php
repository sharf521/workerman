<?php

namespace MyPhp\Model;
use MyPhp\Lib\Model;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/23
 * Time: 16:55
 */
class User extends Model
{
    protected $table='user';
    public function __construct()
    {
        parent::__construct();
    }
}