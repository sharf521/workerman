<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
//error_reporting(E_ALL & ~E_NOTICE);
error_reporting(7);
header('Content-language: zh');
header('Content-type: text/html; charset=utf-8');
header('X-Powered-By: JAVA');
header('Pragma: no-cache');
header('Cache-Control: private', false); // required for certain browsers
header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
//获取域名
$domain = strtolower($_SERVER['HTTP_HOST']);
if (strpos($domain, ':') !== false) {
    //去除端口
    $domain = explode(':', $domain);
    $domain = $domain[0];
}
$domain_arr = explode('.', $domain);
if ($domain_arr[count($domain_arr) - 2] == 'com') {
    $domain = $domain_arr[count($domain_arr) - 3] . '.' . $domain_arr[count($domain_arr) - 2] . '.' . $domain_arr[count($domain_arr) - 1];
} else {
    $domain = $domain_arr[count($domain_arr) - 2] . '.' . $domain_arr[count($domain_arr) - 1];
}
//ini_set('session.cookie_domain', $domain);//域名不需要端口
//ini_set("session.save_handler", "redis");
//ini_set("session.save_path", "tcp://127.0.0.1:6379");
session_cache_limiter('private,must-revalidate');
session_name('HM');
session_start();
date_default_timezone_set('Asia/Shanghai');
set_time_limit($set_time = 3600);
require 'vendor/autoload.php';

define('MyPHP_KEY', 'kee__ewk__ss__sk');
define('ROOT', __DIR__);
define('DB_CONFIG', \App\Config::$db1);
define('DB_CONFIG_FIX', \App\Config::$db1['dbfix']);
define('ADMIN_UID', 2);
$routes = array(
    'ps_system' => 'Admin',
    'member'    => 'Member',
);
\System\Lib\Application::start($routes);
\System\Lib\DB::closeAll();