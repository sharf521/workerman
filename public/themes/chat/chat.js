$(function () {
    $.post("/chat/initUser", IM.user, function (data) {
        if (data.code == 0) {
            var user = data.user;
            IM.user.id = user.id;
            IM.user.token = user.token;
            window.localStorage.setItem('im_token',user.token);
            connect_workerman();
            setInterval('send_heartbeat()', 20000);
        } else {
            alert(data.msg);
        }
    }, 'json');
});

inited = false;
function connect_workerman() {
    socket = new WebSocket('ws://'+IM.ws+'/?token='+IM.user.token);
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
                initim([]);
                return;
            case 'addList':
                if (IM.user.id != msg.data.id) {
                    msg.data.groupid=0;
                    layui.layim.addList(msg.data);
                }
                layui.layim.setFriendStatus(msg.data.id, 'online');
                return;
            case 'chatMessage':
                if (IM.user.id !== msg.data.id) {
                    layui.layim.getMessage(msg.data);
                }
                return;
            case 'hide':
            case 'logout':
                layui.layim.setFriendStatus(msg.id, 'offline');
                return;
            case 'online':
                layui.layim.setFriendStatus(msg.id, 'online');
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
        layim=layui.layim;
        console.log(layim);
        //基础配置
        layim.config({
            //初始化接口
            init: {
                url: '/chat/getList/?uid='+IM.user.id
            }
            //查看群员接口
            , members: {
                url: '/chat/getGroupMembers/?uid='+IM.user.id
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
            //扩展工具栏
            ,tool: [{
                alias: 'code'
                ,title: '代码'
                ,icon: '&#xe64e;'
            }]
            //聊天记录地址
            , chatLog: '/chat/history/'+IM.user.id+'/'
            , find:false
            ,right:'10px'
            ,minRight:'10px'
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
        //监听自定义工具栏点击，以添加代码为例
        layim.on('tool(code)', function(insert){
            layer.prompt({
                title: '插入代码 - 工具栏扩展示例'
                ,formType: 2
                ,shade: 0
            }, function(text, index){
                layer.close(index);
                insert('[pre class=layui-code]' + text + '[/pre]'); //将内容插入到编辑器
            });
        });
        //layim建立就绪
        layim.on('ready', function (res) {
            console.log('ready：'+res);
            // 离线消息
            for (var key in history_message) {
                layim.getMessage(JSON.parse(history_message[key]));
            }
        });
    });
}