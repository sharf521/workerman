<!doctype html>
<html>
<head>
    <title>LayIM移动版测试</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <script src="/themes/chat/js/jquery.min.js"></script>
    <link rel="stylesheet" href="/plugin/layui/css/layui.mobile.css" media="all"/>
</head>
<body>

<script src="/plugin/layui/layui.js"></script>

<script type="text/javascript">
    IM={};
    IM.user = {};
    $(function () {
        $.get("/chat/getUserInfo", {uid: '<?=$_GET['id']?>'}, function (data) {
            if (data.code == 0) {
                var user = data.user;
                IM.user.id = user.id;
                IM.user.avatar = user.avatar;
                IM.user.sign = user.sign;
                IM.user.username = user.username;
                IM.user.token = user.token;
                window.localStorage.setItem('im_token',user.token);
                connect_workerman();
                setInterval('send_heartbeat()', 20000);
            } else {
                alert(data.msg);
            }
        }, 'json');
    });
</script>
<script src="/themes/chat/chat_wap.js"></script>
</body>
</html>