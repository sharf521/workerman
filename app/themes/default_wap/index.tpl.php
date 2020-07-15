<!doctype html>
<html>
<head>
    <title>LayIM移动版测试</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
</head>
<body>
<?
$user=(new \App\Model\ChatUser())->find($_GET['id']);
if(!$user->is_exist){
    $user->id=$_GET['id'];
    $user->avatar='http://lorempixel.com/38/38/?'.$user->id;
    $user->nickname='user'.$user->id;
    $user->save();
}
?>
<script src="/themes/chat/js/jquery.min.js"></script>
<link rel="stylesheet" href="/plugin/layui/css/layui.mobile.css" media="all"/>
<script src="/plugin/layui/layui.js"></script>
<script type="text/javascript">
    IM={};
    IM.ws='<?=\App\Config::$ws_url?>';
    IM.user = {};
    IM.user.id = '<?=$user->id?>';
    IM.user.avatar = '<?=$user->avatar?>';
    IM.user.username = '<?=$user->nickname?>';
    IM.user.sign = '';
</script>
<script src="/themes/chat/chat_wap.js"></script>
</body>
</html>