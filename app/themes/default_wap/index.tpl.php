<!doctype html>
<html>
<head>
    <title>LayIM移动版测试</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <style>
        /*
        body .layim-title{display: none;}
        body .layim-chat-main, body .layim-content, body .layui-layim{top: 0}*/
    </style>
</head>
<body>
<script src="/themes/chat/js/jquery.min.js"></script>
<link rel="stylesheet" href="/plugin/layui/css/layui.mobile.css" media="all"/>
<script src="/plugin/layui/layui.js"></script>
<script type="text/javascript">
    IM={};
    IM.user_id = '<?=$_GET['id']?>';
    IM.app_id = '10';
    IM.user = {};
    IM.user.avatar ='';
    IM.user.username = '';
</script>
<script src="/themes/chat/chat_wap.js"></script>
</body>
</html>