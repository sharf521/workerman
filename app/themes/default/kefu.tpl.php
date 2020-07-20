<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>客服</title>
</head>
<body>
<style>
    .layui-layim-close{display:none}
    .layim-list-gray{-webkit-filter:grayscale(100%);-ms-filter:grayscale(100%);filter:grayscale(100%);filter:gray}
    .laykefu-box{position:fixed;top:320px;right:20px;width:120px;overflow:hidden;z-index: 999}
    .laykefu-box .laykefu-btn{border-radius:100px;box-shadow:0 6px 12px 0 rgba(0,0,0,.15);background-color:#33cde5;line-height:40px;text-align:left;font-size:14px;color:#fff;cursor:pointer;margin:3px 0}
    .laykefu-box .laykefu-btn img{border-radius:100px;width:30px;height:30px;margin:0 10px}
</style>

<script src="/IM_URL/themes/chat/js/jquery.min.js"></script>
<link rel="stylesheet" href="/IM_URL/plugin/layui/css/layui.css"/>
<script src="/IM_URL/plugin/layui/layui.js"></script>
<script type="text/javascript">
    IM={};
    IM.user_id=window.localStorage.getItem('IM_session');
    if(!IM.user_id){
        IM.user_id='<?=session_id()?>';
        window.localStorage.setItem('IM_session',IM.user_id);
    }
    IM.app_id = '10';
    IM.user = {};
    IM.user.avatar ='';
    IM.user.username = '访客';
    IM.serviceList=[{
        name: '客服1'
        ,type: 'friend'
        ,avatar: 'https://www.laykefu.com/uploads/20190419/4eb84234138339f27018e1e3625afd15.jpg'
        ,id: 1
    },{
        name: '客服2'
        ,type: 'friend'
        ,avatar: 'https://www.laykefu.com/uploads/20190419/4eb84234138339f27018e1e3625afd15.jpg'
        ,id: 2
    }];
    var IM_html='<div class="laykefu-box">\n';
    IM.serviceList.forEach(function (v) {
        IM_html+='<div class="laykefu-btn laykefu'+v.id+'" onclick="javascript:openKeFu('+v.id+');"><img src="'+v.avatar+'">'+v.name+'</div>\n';
    });
    IM_html+='</div>';
    $('body').append(IM_html);
</script>
<script src="/themes/chat/chat_kefu.js"></script>
</body>
</html>