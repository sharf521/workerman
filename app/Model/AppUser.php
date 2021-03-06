<?php

namespace App\Model;

use System\Lib\DB;

class AppUser extends Model
{
    protected $table = 'app_user';

    public function __construct()
    {
        parent::__construct();
    }

    public function getUser($user_id, $app_id)
    {
        $user = $this->where("user_id='{$user_id}' and app_id={$app_id}")->first();
        return $user;
    }

    public function getUserOrCreate($user_id, $app_id, $nickname = '', $avatar = '', $sign = '')
    {
        $user = $this->getUser($user_id, $app_id);
        if (!$user->is_exist) {
            if (empty($avatar)) {
                $avatar = 'http://lorempixel.com/38/38/?' . $user_id;
            }
            if (empty($nickname)) {
                $nickname = 'user' . $user_id;
            }
            $user->avatar   = $avatar;
            $user->nickname = $nickname;

            $user->user_id = $user_id;
            $user->app_id  = $app_id;
            $user->openid  = $this->createOpenId($user_id, $app_id);
            $user->sign    = $sign;
            $id            = $user->save(true);
        } else {
            if (!empty($nickname)) {
                $user->nickname = $nickname;
            }
            if (!empty($avatar)) {
                $user->avatar = $avatar;
            }
            if(!empty($nickname) || !empty($avatar)){
                $user->save();
            }
            $id = $user->id;
        }
        return array(
            'id'       => $id,
            'nickname' => $user->nickname,
            'avatar'   => $user->avatar,
            'sign'     => $user->sign
        );
    }

    public function getOpenId($user_id, $app_id)
    {
        return DB::table('app_user')->where("user_id={$user_id} and app_id=?")->bindValues($app_id)->value('openid');
    }

    public function createOpenId()
    {
        $uuid   = uniqid(rand(100000000, 999999999), true);
        $openid = str_replace('.', '', $uuid);
        return $openid;
    }
}