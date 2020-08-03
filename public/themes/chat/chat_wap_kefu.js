$(function () {
    $.post("/imApi/initUser", {
        user_id: IM.user_id,
        app_id: IM.app_id,
        nickname: IM.user.username,
        avatar: IM.user.avatar,
        sign:IM.user.sign
    }, function (data) {
        if (data.code == 0) {
            IM.ws = data.ws;
            var user = data.user;
            IM.user.id = user.id;
            IM.user.token = user.token;
            IM.user.avatar = user.avatar;
            IM.user.username = user.nickname;
            IM.user.sign = user.sign;
            window.localStorage.setItem('im_token', user.token);
            connect_workerman();
            setInterval('send_heartbeat()', 20000);
        } else {
            console.log('initUser Error:'+data.msg);
        }
    }, 'json');
});


IM.inited = false;

function connect_workerman() {
    console.log('ws://' + IM.ws + '/?token=' + IM.user.token);
    socket = new WebSocket(IM.ws + '/?token=' + IM.user.token);
    socket.onopen = function () {
        var initStr = IM.user;
        initStr['serviceIds'] = [IM.toUser.id];
        initStr['type'] = 'init';
        socket.send(JSON.stringify(initStr));
        console.log("onopen:" + JSON.stringify(initStr));
    };
    socket.onmessage = function (res) {
        console.log("onmessage:" + res.data);
        var msg = JSON.parse(res.data);
        switch (msg.message_type) {
            case 'init':
                IM.online_list = msg.online_list;
                initLayIM();
                return;
            case 'chatMessage':
                if (IM.user.id !== msg.data.id) {
                    layui.mobile.layim.getMessage(msg.data);
                }
                return;
            case 'hide':
            case 'logout':
                if (msg.id == IM.toUser.id) {
                    layui.mobile.layim.getMessage({
                        system: true //系统消息
                        , id: IM.toUser.id //聊天窗口ID
                        , type: "friend" //聊天窗口类型
                        , content: '对方已下线'
                    });
                    layui.mobile.layim.setFriendStatus(msg.id, 'offline'); //设置指定好友在线，即头像置灰
                }
                return;
            case 'addList':
            case 'online':
                if (msg.id == IM.toUser.id || msg.data.id == IM.toUser.id) {
                    layui.mobile.layim.getMessage({
                        system: true //系统消息
                        , id: IM.toUser.id //聊天窗口ID
                        , type: "friend" //聊天窗口类型
                        , content: '对方上线'
                    });
                    layui.mobile.layim.setFriendStatus(msg.id, 'online'); //设置指定好友在线，即头像置灰
                }
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
    layui.use(['laypage', 'laytpl', 'mobile'], function () {
        var mobile = layui.mobile
            , layim = mobile.layim
            , layer = mobile.layer
            , laypage = layui.laypage
            , laytpl = layui.laytpl;
        console.log(layim);
        //提示层
        layer.msg = function (content) {
            return layer.open({
                content: content
                , skin: 'msg'
                , time: 2 //2秒后自动关闭
            });
        };
        //删除记录
        var local = layui.data('layim-mobile')[IM.user.id]; //获取当前用户本地数据
        if(local && local.chatlog){
            delete local.chatlog['friend'+IM.toUser.id];
        }
        //向localStorage同步数据
        layui.data('layim-mobile', {
            key: IM.user.id
            ,value: local
        });

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
            }
            // 上传图片
            , uploadImage: {
                url: '/pictureApi/memberApi/upload/save?type=chat&token=' + window.localStorage.getItem('im_token')
            }
            , isAudio: true //开启聊天工具栏音频
            , isVideo: true //开启聊天工具栏视频
            , title: 'MyChat'
            //,isNewFriend: false //是否开启“新的朋友”
            , isgroup: false //是否开启“群聊”
            , brief: true
            //,chatTitleColor: '#c00' //顶部Bar颜色
        });

        layui.mobile.layim.chat(IM.toUser);




        //监听发送消息
        layim.on('sendMessage', function (data) {
            socket.send(JSON.stringify({type: 'chatMessage', data: data}));
            console.log("sendMessage:" + JSON.stringify({type: 'chatMessage', data: data}));
        });
        //监听在线状态的切换事件
        layim.on('online', function (data) {
            socket.send(JSON.stringify({type: data}));
        });

        function myChatLog(data, curr) {
            $.get('/imApi/chatLog/' + IM.user.token + '/', {id: data.id, type: data.type, page: curr}, function (res) {
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
                if ($(".layim-chat-log").length > 0) {
                    laytpl(chatlogTpl).render(res, function (html) {
                        $(".layim-chat-log ul").html(html)
                    });
                } else {
                    layim.panel({
                        title: '与 ' + data.name + ' 的聊天记录'
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
                                myChatLog(data, obj.curr);
                            }
                        }
                    });
                }
            }, 'json');
        }

        //监听查看更多记录
        layim.on('chatlog', function (data, ul) {
            console.log(data); //得到当前会话对象的基本信息
            console.log(ul); //得到当前聊天列表所在的ul容器，比如可以借助他来实现往上插入更多记录
            myChatLog(data, 1);
        });

        //监听返回
        layim.on('back', function () {
            //比如返回到上一页面（而不是界面）：
            //history.back();
            //或者做一些其他的事
        });

        // 记录缓存已清除，拉取显示历史记录
        $.get('/imApi/chatLog/' + IM.user.token + '/', {id: IM.toUser.id, type: 'friend', page: 1,pageSize:5}, function (res) {
            var data = res.rows;
            var html = '';
            for (var key in data) {
                if (IM.user.id == data[key].id) {
                    html += '<li class="layim-chat-mine"><div class="layim-chat-user"><img src="' + data[key].avatar + '"><cite><i>' + data[key].created_at + '</i>' + data[key].username + '</cite></div><div class="layim-chat-text">' + layim.content(data[key].content) + '</div></li>';
                } else {
                    html += '<li><div class="layim-chat-user"><img src="' + data[key].avatar + '"><cite>' + data[key].username + '<i>' + data[key].created_at + '</i></cite></div><div class="layim-chat-text">' + layim.content(data[key].content) + '</div></li>';
                }
            }
            $(".layim-chat-main ul").html(html);

            if (IM.online_list.includes(IM.toUser.id)) {
                layim.getMessage({
                    system: true //系统消息
                    , id: IM.toUser.id //聊天窗口ID
                    , type: "friend" //聊天窗口类型
                    , content: '对方在线'
                });
            } else {
                layim.getMessage({
                    system: true //系统消息
                    , id: IM.toUser.id //聊天窗口ID
                    , type: "friend" //聊天窗口类型
                    , content: '对方已下线'
                });
            }
        }, 'json');


    });
}