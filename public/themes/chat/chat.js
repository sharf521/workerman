// 浏览器不支持websocket则自动用flash模拟
WEB_SOCKET_SWF_LOCATION = "/themes/chat/swf/WebSocketMain.swf";
WEB_SOCKET_DEBUG = true;
inited = false;
connect_workerman();
setInterval('send_heartbeat()', 20000);
function connect_workerman() {
    socket = new WebSocket('ws://127.0.0.1:7272/?id='+userinfo.id);
    socket.onopen = function () {
        var initStr = userinfo;
        initStr['type'] = 'init';
        socket.send(JSON.stringify(initStr));
        console.log("onopen:" + JSON.stringify(initStr));
    };
    socket.onmessage = function (res) {
        console.log("onmessage:" + res.data);
        var msg = JSON.parse(res.data);
        switch (msg.message_type) {
            case 'init':
                var login_info = {
                    id: userinfo['id'],
                    username: userinfo['username'],
                    sign: userinfo['sign'],
                    avatar: userinfo['avatar'],
                    client_id: msg.client_id
                };
                initim([]);
//                    $.post("./login.php", login_info, function (data) {
//                        if (data.code == 0) {
//                            initim(data.history_message);
//                        } else {
//                            alert(data.msg);
//                        }
//                    }, 'json');
                return;
            case 'addList':
                if ($('#layim-friend' + msg.data.id).length == 0 && userinfo['id'] != msg.data.id) {
                    return layui.layim.addList(msg.data);
                }
                $('#layim-friend' + msg.data.id + ' img').removeClass('gray_icon');
                return;
            case 'chatMessage':
                if (userinfo['id'] !== msg.data.id) {
                    layui.layim.getMessage(msg.data);
                }
                return;
            case 'logout':
            case 'hide':
            case 'online':
                var status = msg.message_type;
                change_online_status(msg.id, status);
                return;
        }
    }
    socket.onclose = connect_workerman;
}

// 发送心跳，防止链接长时间空闲被防火墙关闭
function send_heartbeat() {
    if (socket && socket.readyState == 1) {
        socket.send(JSON.stringify({type: 'ping'}));
    }
}

function change_online_status(id, status) {
    if (status === 'hide' || status === 'logout') {
        return $('#layim-friend' + id + ' img').addClass('gray_icon');
    }
    $('#layim-friend' + id + ' img').removeClass('gray_icon');
}

function add_history_tip() {
    $('.layim-chat-main ul').append('<li><div class="history-tip">以上是历史消息</div></li>');
}

// 初始化聊天窗口
function initim(history_message) {
    if (inited) {
        // 离线消息
        for (var key in history_message) {
            layui.layim.getMessage(JSON.parse(history_message[key]));
        }
        return;
    }
    inited = true;
    layui.use('layim', function (layim) {
        console.log(layim);
        //基础配置
        layim.config({
            //初始化接口
            init: {
                url: '/chat/getList/?uid='+userinfo['id']
            }
            //查看群员接口
            , members: {
                url: '/chat/getMembers/?uid='+userinfo['id']
            }
            // 上传图片
            , uploadImage: {
                url: 'http://wechatwap.test.cn:8000/upload/save/?type=chat'
            }
            // 上传文件
            , uploadFile: {
                url: './upload_file.php'
            }
            //聊天记录地址
            , chatLog: '/chat/history/'+userinfo['id']+'/'
            , find:false
            , copyright: true //是否授权
            , title: 'LayChat'
        });
        //监听发送消息
        layim.on('sendMessage', function (data) {
            $.post("/chat/post_message/", {data: data});
            socket.send(JSON.stringify({type: 'chatMessage',data:data}));
            console.log("sendMessage:" + JSON.stringify({type: 'chatMessage',data:data}));
        });
        //监听在线状态的切换事件
        layim.on('online', function (data) {
            socket.send(JSON.stringify({type: data}));
        });
        //layim建立就绪
        layim.on('ready', function (res) {
            console.log('ready：'+res);
            // 离线消息
            for (var key in history_message) {
                layim.getMessage(JSON.parse(history_message[key]));
            }
//                layui.layim.getMessage({
//                    username: "Hi"
//                    ,avatar: "http://tva1.sinaimg.cn/crop.7.0.736.736.50/bd986d61jw8f5x8bqtp00j20ku0kgabx.jpg"
//                    ,id: "198909151014"
//                    ,type: "friend"
//                    ,content: "临时："
//                });

            // 将不在线的置为下线
            var friend_list = res.friend[0].list;
            for (var key in friend_list) {
                var user_id = friend_list[key].id;
                change_online_status(user_id, friend_list[key]['status']);
            }
        });
    });
}