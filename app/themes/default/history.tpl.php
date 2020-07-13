<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>聊天历史记录</title>
    <script src="/themes/chat/js/jquery.min.js"></script>
    <link rel="stylesheet" href="/plugin/layui/css/layui.css" />
    <script src="/plugin/layui/layui.js"></script>
    <link rel="stylesheet" href="/themes/chat/chat.css">
    <style>
        body{overflow-x: hidden}
    </style>
</head>
<body>
<script>
    var uid="<?=$uid?>";
    var data = eval(<?=$data?>);
    layui.use(['layim'], function(){
        var layim = layui.layim;
        var html = '';
        for(var key in data){
            if(uid==data[key].id){
                html += '<li class="layim-chat-mine"><div class="layim-chat-user"><img src="'+data[key].avatar+'"><cite><i>'+data[key].created_at+'</i>'+data[key].username+'</cite></div><div class="layim-chat-text">'+ layim.content(data[key].content)+'</div></li>';
            }else{
                html += '<li><div class="layim-chat-user"><img src="'+data[key].avatar+'"><cite>'+data[key].username+'<i>'+data[key].created_at+'</i></cite></div><div class="layim-chat-text">'+ layim.content(data[key].content)+'</div></li>';
            }
        }
        $(".layim-chat-main ul").append(html);
    });
</script>
<div class="layim-chat-friend">
    <div class="layim-chat-main" style="height:100%">
        <ul>

        </ul>
    </div>
    <?=$page;?>
</div>
</body>
</html>