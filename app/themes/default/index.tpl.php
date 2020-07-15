<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>layim - layui</title>
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
<script src="/IM_URL/themes/chat/js/jquery.min.js"></script>
<link rel="stylesheet" href="/IM_URL/plugin/layui/css/layui.css"/>
<script src="/IM_URL/plugin/layui/layui.js"></script>
<script type="text/javascript">
    IM={};
    IM.ws='<?=\App\Config::$ws_url?>';
    IM.user = {};
    IM.user.id = '<?=$user->id?>';
    IM.user.avatar = '<?=$user->avatar?>';
    IM.user.username = '<?=$user->nickname?>';
    IM.user.sign = '';
</script>
<script src="/themes/chat/chat.js"></script>
</body>
</html>