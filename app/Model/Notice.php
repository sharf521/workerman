<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/25
 * Time: 16:21
 */

namespace App\Model;


class Notice extends Model
{
    protected $table='notice';
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return AppUser
     */
    public function AppUser()
    {
        if($this->user_id!=0){
            return (new AppUser())->find($this->user_id);
        }else{
            return new AppUser();
        }
    }
}