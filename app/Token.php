<?php

namespace App;


use Firebase\JWT\JWT;

class Token
{
    private static $key = 'my_key_zz';

    public static function createToken($uid, $day = 0.5)
    {
        $token = array(
            'uid' => $uid,
            "iss" => "http://www.abcxxc.org",
            "aud" => "http://www.abcxxc.com",
            "iat" => time(),
            "exp" => time() + 3600 * 24 * $day
        );
        $jwt   = JWT::encode($token, self::$key);
        return $jwt;
    }

    public static function getUid($token = '')
    {
        if ($token == '') {
            $token = htmlspecialchars($_SERVER['HTTP_AUTHORIZATION']);
        }
        try {
            $decoded = (array)JWT::decode($token, self::$key, array('HS256'));
            $uid     = (int)$decoded['uid'];
            return $uid;
        } catch (\Exception $e) {
            return 0;
        }
    }
}