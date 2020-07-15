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

    public function getOpenId($user_id, $app_id)
    {
        return DB::table('app_user')->where("user_id={$user_id} and app_id=?")->bindValues($app_id)->value('openid');
    }

    public function create($user_id, $app_id)
    {
        $openid = $this->createOpenId();
        $arr = array(
            'user_id' => $user_id,
            'app_id' => $app_id,
            'openid' => $openid,
            'created_at' => time()
        );
        DB::table('app_user')->insert($arr);
        return $openid;
    }

    private function createOpenId()
    {
        $uuid = uniqid(rand(100000000, 999999999), true);
        $openid = str_replace('.', '', $uuid);
        return $openid;
    }
}