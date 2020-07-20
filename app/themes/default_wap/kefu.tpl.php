<!doctype html>
<html>
<head>
    <title>LayIM移动版测试</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <style>
        /** 分页 **/
        .layui-laypage{display: inline-block; *display: inline; *zoom: 1; vertical-align: middle; margin: 10px 0; font-size: 0;}
        .layui-laypage>a:first-child,
        .layui-laypage>a:first-child em{border-radius: 2px 0 0 2px;}
        .layui-laypage>a:last-child,
        .layui-laypage>a:last-child em{border-radius: 0 2px 2px 0;}
        .layui-laypage>*:first-child{margin-left: 0!important;}
        .layui-laypage>*:last-child{margin-right: 0!important;}
        .layui-laypage a,
        .layui-laypage span,
        .layui-laypage input,
        .layui-laypage button,
        .layui-laypage select{border: 1px solid #e2e2e2;}
        .layui-laypage a,
        .layui-laypage span{display: inline-block; *display: inline; *zoom: 1; vertical-align: middle; padding: 0 15px; height: 28px; line-height: 28px; margin: 0 -1px 5px 0; background-color: #fff; color: #333; font-size: 12px;}
        .layui-laypage a:hover{color: #009688;}
        .layui-laypage em{font-style: normal;}
        .layui-laypage .layui-laypage-spr{color:#999; font-weight: 700;}
        .layui-laypage a{ text-decoration: none;}
        .layui-laypage .layui-laypage-curr{position: relative;}
        .layui-laypage .layui-laypage-curr em{position: relative; color: #fff;}
        .layui-laypage .layui-laypage-curr .layui-laypage-em{position: absolute; left: -1px; top: -1px; padding: 1px; width: 100%; height: 100%; background-color: #009688; }
        .layui-laypage-em{border-radius: 2px;}
        .layui-laypage-prev em,
        .layui-laypage-next em{font-family: Sim sun; font-size: 16px;}

        .layui-laypage .layui-laypage-count,
        .layui-laypage .layui-laypage-limits,
        .layui-laypage .layui-laypage-refresh,
        .layui-laypage .layui-laypage-skip{margin-left: 10px; margin-right: 10px; padding: 0; border: none;}
        .layui-laypage .layui-laypage-limits,
        .layui-laypage .layui-laypage-refresh{vertical-align: top;}
        .layui-laypage .layui-laypage-refresh i{font-size: 18px; cursor: pointer;}
        .layui-laypage select{height: 22px; padding: 3px; border-radius: 2px; cursor: pointer;}
        .layui-laypage .layui-laypage-skip{height: 30px; line-height: 30px; color: #999;}
        .layui-laypage input, .layui-laypage button{height: 30px; line-height: 30px; border-radius: 2px; vertical-align: top;  background-color: #fff; box-sizing: border-box;}
        .layui-laypage input{display: inline-block; width: 40px; margin: 0 10px; padding: 0 3px; text-align: center;}
        .layui-laypage input:focus,
        .layui-laypage select:focus{border-color: #009688!important;}
        .layui-laypage button{margin-left: 10px; padding: 0 10px; cursor: pointer;}
        /*
        body .layim-title{display: none;}
        body .layim-chat-main, body .layim-content, body .layui-layim{top: 0}*/
    </style>
</head>
<body>
<script src="/themes/chat/js/jquery.min.js"></script>
<link rel="stylesheet" href="/plugin/layui/css/layui.mobile.css" media="all"/>
<link rel="stylesheet" href="/plugin/layui/css/modules/layim/mobile/layim.css" media="all">

<script src="/plugin/layui/layui.js"></script>
<script type="text/javascript">
    IM={};
    IM.user_id = '<?=$_GET['id']?>';
    IM.app_id = '10';
    IM.user = {};
    IM.user.avatar ='';
    IM.user.username = '';
    <?
    $toUser=(new \App\Model\AppUser())->getUser($_GET['to_uid'],10);
    ?>
    IM.toUser =
        {
            name: '<?=$toUser->nickname?>'
            , type: 'friend'
            , avatar: '<?=$toUser->avatar?>'
            , id: '<?=$toUser->id?>'
        }
</script>
<script src="/themes/chat/chat_wap_kefu.js"></script>
</body>
</html>