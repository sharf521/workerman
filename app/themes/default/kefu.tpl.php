<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>layim - layui</title>

</head>
<body>
<style>
    .layui-layim-close{display: none}
    .layim-list-gray{-webkit-filter: grayscale(100%);  -ms-filter: grayscale(100%); filter: grayscale(100%); filter: gray;}
</style>
<input type="button" onclick="open1()" value="客服1">
<input type="button" onclick="open2()" value="客服2">

<script src="/IM_URL/themes/chat/js/jquery.min.js"></script>
<link rel="stylesheet" href="/IM_URL/plugin/layui/css/layui.css"/>
<script src="/IM_URL/plugin/layui/layui.js"></script>
<script type="text/javascript">
    IM={};
    IM.user_id = '<?=session_id()?>';
    IM.app_id = '10';
    IM.user = {};
    IM.user.avatar ='';
    IM.user.username = '访客';
    IM.serviceIds=[1,2];
    function open1() {
        //自定义会话
        layui.layim.chat({
            name: '客服1'
            ,type: 'friend'
            ,avatar: '//tva3.sinaimg.cn/crop.0.0.180.180.180/7f5f6861jw1e8qgp5bmzyj2050050aa8.jpg'
            ,id: 1
        });
    }
    function open2() {
        //自定义会话
        layui.layim.chat({
            name: '客服2'
            ,type: 'friend'
            ,avatar: '//tva3.sinaimg.cn/crop.0.0.180.180.180/7f5f6861jw1e8qgp5bmzyj2050050aa8.jpg'
            ,id: 2
        });
    }
</script>
<script src="/themes/chat/chat_kefu.js"></script>
</body>
</html>