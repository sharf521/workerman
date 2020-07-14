<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>layim - layui</title>
</head>
<body>
<?
$user=(new \App\Model\User())->find($_GET['id']);
?>
<script src="/themes/chat/js/jquery.min.js"></script>
<link rel="stylesheet" href="/plugin/layui/css/layui.css"/>
<script src="/plugin/layui/layui.js"></script>
<script type="text/javascript">
    IM={};
    IM.ws='127.0.0.1:7272';
    IM.user = {};
    IM.user.id = '<?=$user->id?>';
    IM.user.avatar = '<?=$user->headimgurl?>';
    IM.user.username = '<?=$user->nickname?>';
    IM.user.sign = '';
</script>
<script src="/themes/chat/chat.js"></script>
</body>
</html>