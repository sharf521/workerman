<!doctype html>
<html>
<head>
    <title>LayIM移动版测试</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
</head>
<body>
<?
$user=(new \App\Model\User())->find($_GET['id']);
?>
<script src="/themes/chat/js/jquery.min.js"></script>
<link rel="stylesheet" href="/plugin/layui/css/layui.mobile.css" media="all"/>
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
<script src="/themes/chat/chat_wap.js"></script>
</body>
</html>