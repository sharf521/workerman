<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>聊天历史记录</title>
    <script src="/IM_URL/themes/chat/js/jquery.min.js"></script>
    <link rel="stylesheet" href="/IM_URL/plugin/layui/css/layui.css" />
    <script src="/IM_URL/plugin/layui/layui.js"></script>
    <style>
        body{overflow-x: hidden}
        .page{padding-top:10px}
        .p_bar {clear:both;padding:10px 0; text-align:center; font-size:14px; color: #0e0e0e; height: 30px; overflow: hidden;}
        .p_info { border:1px solid #dddddd; padding:4px 10px;}
        .p_bar a {text-decoration:none;padding:4px 10px; color: #0e0e0e;}
        .p_bar a:hover {background:#dddddd;border:1px solid #dddddd; border-left: 0px;text-decoration:none;}
        .p_num {background:#FFF;border:1px solid #dddddd; border-left: 0px;}
        .p_redirect {background:#FFF;border:1px solid #dddddd; border-left: 0px; padding:4px 10px;}
        .p_curpage {border:1px solid #337ab7; border-left: 0px;background:#337ab7;color:#fff; padding:4px 10px;}
    </style>
</head>
<body>
<script>
    var uid="<?=$uid?>";
    var data = eval(<?=$data?>);
    layui.use(['layim','laypage'], function(){
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
        //执行一个laypage实例
        /*layui.laypage.render({
            elem: 'page_box'
            ,count: '50' //数据总数，从服务端得到
            ,jump: function(obj, first){
                //obj包含了当前分页的所有参数，比如：
                console.log(obj.curr); //得到当前页，以便向服务端请求对应页的数据。
                console.log(obj.limit); //得到每页显示的条数

                $.get(window.location.href, {page: obj.curr}, function (data) {
                    if (data.code == 0) {

                    } else {
                        alert(data.msg);
                    }
                }, 'json');

                //首次不执行
                if(!first){
                    //do something
                }
            }
        });*/

    });

</script>
<div class="layim-chat-friend">
    <div class="layim-chat-main" style="height:100%">
        <ul>

        </ul>
    </div>
    <?=$page;?>
    <div id="page_box"></div>
</div>
</body>
</html>