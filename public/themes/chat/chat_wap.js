$(function () {
    $.post("/imApi/initUser", {user_id:IM.user_id,app_id:IM.app_id,nickname:IM.user.username,avatar:IM.user.avatar}, function (data) {
        if (data.code == 0) {
            IM.ws=data.ws;
            var user = data.user;
            IM.user.id = user.id;
            IM.user.token = user.token;
            IM.user.avatar = user.avatar;
            IM.user.username = user.nickname;
            IM.user.sign =user.sign;
            window.localStorage.setItem('im_token',user.token);
            connect_workerman();
            setInterval('send_heartbeat()', 20000);
        } else {
            alert(data.msg);
        }
    }, 'json');
});

IM.inited = false;
function connect_workerman() {
    console.log('ws://'+IM.ws+'/?token='+IM.user.token);
    socket = new WebSocket(IM.ws+'/?token='+IM.user.token);
    socket.onopen = function () {
        var initStr = IM.user;
        initStr['type'] = 'init';
        socket.send(JSON.stringify(initStr));
        console.log("onopen:" + JSON.stringify(initStr));
    };
    socket.onmessage = function (res) {
        console.log("onmessage:" + res.data);
        var msg = JSON.parse(res.data);
        switch (msg.message_type) {
            case 'init':
                IM.online_list=msg.online_list;
                initLayIM();
                return;
            case 'addList':
                if (IM.user.id != msg.data.id && $('.layim-list-friend').find('.layim-friend' + msg.data.id).length==0) {
                    msg.data.groupid=0;
                    layui.mobile.layim.addList(msg.data);
                }
                layui.mobile.layim.setFriendStatus(msg.data.id, 'online');
                return;
            case 'chatMessage':
                if (IM.user.id !== msg.data.id) {
                    layui.mobile.layim.getMessage(msg.data);
                }
                return;
            case 'hide':
                layui.mobile.layim.setFriendStatus(msg.id, 'offline'); //设置指定好友在线，即头像置灰
                return;
            case 'logout':
                layui.mobile.layim.setFriendStatus(msg.id, 'offline'); //设置指定好友在线，即头像置灰
                layui.mobile.layim.removeList({id: msg.id, type: 'friend'});
                return;
            case 'online':
                layui.mobile.layim.setFriendStatus(msg.id, 'online'); //设置指定好友在线，即头像取消置灰
                return;
        }
    };
    socket.onclose = connect_workerman;
}

// 发送心跳，防止链接长时间空闲被防火墙关闭
function send_heartbeat() {
    if (socket && socket.readyState == 1) {
        socket.send(JSON.stringify({type: 'ping'}));
    }
}

// 初始化聊天窗口
function initLayIM() {
    if (IM.inited) {
        return;
    }
    IM.inited = true;
    layui.use(['laypage','laytpl','mobile'], function () {
        var mobile = layui.mobile
            , layim = mobile.layim
            , layer = mobile.layer
            , laypage = layui.laypage
            , laytpl = layui.laytpl;
        console.log(layim);
        //提示层
        layer.msg = function(content){
            return layer.open({
                content: content
                ,skin: 'msg'
                ,time: 2 //2秒后自动关闭
            });
        };
        //基础配置
        layim.config({
            //初始化接口
            init: {
                //设置我的基础信息
                mine: {
                    "username": IM.user.username //我的昵称
                    , "id": IM.user.id //我的ID
                    , "avatar": IM.user.avatar //我的头像
                    , "sign": IM.user.sign
                    , "status": "online"
                }
                ,friend: [{
                    "groupname": "在线列表"
                    ,"id": 0
                    ,"list": IM.online_list
                }]
                ,"group": [{
                    "groupname": "在线群"
                    ,"id": "101"
                    ,"avatar": "http://tp2.sinaimg.cn/2211874245/180/40050524279/0"
                }]
            }
            // 上传图片
            , uploadImage: {
                url: '/pictureApi/memberApi/upload/save?type=chat&token='+window.localStorage.getItem('im_token')
            }
            // 上传文件
            , uploadFile: {
                url: '/pictureApi/memberApi/upload/save?type=chat&token='+window.localStorage.getItem('im_token')
            }
            ,isAudio: true //开启聊天工具栏音频
            ,isVideo: true //开启聊天工具栏视频
            //可同时配置多个
            ,moreList: [{
                alias: 'find'
                ,title: '发现'
                ,iconUnicode: '&#xe628;' //图标字体的unicode，可不填
                ,iconClass: '' //图标字体的class类名
            },{
                alias: 'share'
                ,title: '分享'
                ,iconUnicode: '' //图标字体的unicode，可不填
                ,iconClass: '' //图标字体的class类名
            }]
            , title: 'MyChat'
            //,isNewFriend: false //是否开启“新的朋友”
            ,isgroup: true //是否开启“群聊”
            //,chatTitleColor: '#c00' //顶部Bar颜色
        });
        //监听发送消息
        layim.on('sendMessage', function (data) {
            //$.post("/imApi/post_message/", {data: data});
            socket.send(JSON.stringify({type: 'chatMessage',data:data}));
            console.log("sendMessage:" + JSON.stringify({type: 'chatMessage',data:data}));
        });
        //监听在线状态的切换事件
        layim.on('online', function (data) {
            socket.send(JSON.stringify({type: data}));
        });

        //监听点击“新的朋友”
        layim.on('newFriend', function(){
            layim.panel({
                title: '新的朋友' //标题
                ,tpl: '<div style="padding: 10px;">自定义模版，{{d.data.test}}</div>' //模版
                ,data: { //数据
                    test: '么么哒'
                }
            });
        });

        function myChatLog(data,curr){
            $.get('/imApi/chatLog/'+IM.user.token+'/',{id:data.id,type:data.type,page:curr},function(res){
                //var mine = layim.cache().mine,
                var chatlogTpl = ['{{#  layui.each(d.rows, function(index, item){ }}',
                    '<li class="layim-chat-system"><span>{{ item.created_at }}</span></li>',
                    '	    {{# if(item.id==mine.id){ }}',
                    '	    	<li class="layim-chat-li layim-chat-mine">',
                    '	    {{# }else{ }}',
                    '	    	<li class="layim-chat-li">',
                    '	 	{{# } }}',
                    '	 		<div class="layim-chat-user"><img src="{{ item.avatar }}"><cite>{{ item.username }}</cite></div>',
                    '	 		<div class="layim-chat-text">{{layui.mobile.layim.content(item.content) }}</div>',
                    '	    </li>',
                    '	  {{#  }); }}'].join('');

                var mchatlogdom = ['<div class="layim-chat-main layim-chat-log"><ul>',
                    '{{#  layui.each(d.data.rows, function(index, item){ }}',
                    '<li class="layim-chat-system"><span>{{ item.created_at }}</span></li>',
                    '	    {{# if(item.id==mine.id){ }}',
                    '	    	<li class="layim-chat-li layim-chat-mine">',
                    '	    {{# }else{ }}',
                    '	    	<li class="layim-chat-li">',
                    '	 	{{# } }}',
                    '	 		<div class="layim-chat-user"><img src="{{ item.avatar }}"><cite>{{ item.username }}</cite></div>',
                    '	 		<div class="layim-chat-text">{{layui.mobile.layim.content(item.content) }}</div>',
                    '	    </li>',
                    '	  {{#  }); }}',
                    '</ul>',
                    '<div id="pages"></div>',
                    '</div>'].join("");
                //debugger;
                if($(".layim-chat-log").length>0){
                    laytpl(chatlogTpl).render(res, function(html){
                        $(".layim-chat-log ul").html(html)
                    });
                }else{
                    layim.panel({
                        title: (data.type == 'friend'?'与 ' + data.username :data.groupname)+' 的聊天记录'
                        , tpl: mchatlogdom //模版
                        , data: res
                    });
                }
                if (res.total > 10) {
                    laypage.render({
                        elem: 'pages'
                        , count: res.total
                        , curr: curr
                        , limit: 10
                        , layout: ['page']
                        , jump: function (obj, first) {
                            //首次不执行
                            if (!first) {
                                myChatLog(data,obj.curr);
                            }
                        }
                    });
                }
            },'json');
        }

        //查看聊天信息
        layim.on('detail', function(data){
            console.log(data); //获取当前会话对象
            myChatLog(data,1);
        });

        //监听查看更多记录
        layim.on('chatlog', function(data, ul){
            console.log(data); //得到当前会话对象的基本信息
            console.log(ul); //得到当前聊天列表所在的ul容器，比如可以借助他来实现往上插入更多记录
            myChatLog(data,1);

        });


        //监听点击更多列表
        layim.on('moreList', function(obj){
            switch(obj.alias){ //alias即为上述配置对应的alias
                case 'find': //发现
                    layer.msg('自定义发现动作');
                    break;
                case 'share':
                    layim.panel({
                        title: 'share' //分享
                        ,tpl: '<div style="padding: 10px;">自定义模版，{{d.data.test}}</div>' //模版
                        ,data: { //数据
                            test: '123'
                        }
                    });
                    break;
            }
        });

        // 离线消息
        $.post("/imApi/getOffLineMsg", {uid:IM.user.id}, function (data) {
            if (data.code == 0) {
                var history_message=data.data;
                for (var key in history_message) {
                    console.log(history_message[key]);
                    layim.getMessage(history_message[key]);
                }
            } else {
                alert(data.msg);
            }
        }, 'json');
    });
}