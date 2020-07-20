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
                initLayIM();
                return;
            case 'addList':
                if (IM.user.id != msg.data.id && $('.layim-list-friend').find('.layim-friend' + msg.data.id).length==0) {
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
                layui.layim.setFriendStatus(msg.id, 'offline');
                break;
            case 'logout':
                layui.layim.setFriendStatus(msg.id, 'offline');
                layui.layim.removeList({id: msg.id, type: 'friend'});
                break;
            case 'online':
                layui.layim.setFriendStatus(msg.id, 'online');
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

function add_history_tip() {
    $('.layim-chat-main ul').append('<li><div class="history-tip">以上是历史消息</div></li>');
}

// 初始化聊天窗口
function initLayIM() {
    if (IM.inited) {
        return;
    }
    IM.inited = true;
    layui.use('layim', function (layim) {
        layim=layui.layim;
        console.log(layim);
        //基础配置
        layim.config({
            //初始化接口
            init: {
                url: '/imApi/getList/?uid='+IM.user.id
            }
            //查看群员接口
            , members: {
                url: '/imApi/getGroupMembers/?uid='+IM.user.id
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
            , chatLog: '/chat/history/'+IM.user.token+'/'
            , find:false
            , right:'20px'
            , copyright: true //是否授权
            ,min:false
            , title: 'LayChat'
        });
        //监听发送消息
        layim.on('sendMessage', function (data) {
            socket.send(JSON.stringify({type: 'chatMessage',data:data}));
            console.log("sendMessage:" + JSON.stringify({type: 'chatMessage',data:data}));
        });
        layim.on('sign', function(value){
            console.log(value); //获得新的签名
            $.post("/imApi/changSign/", {uid:IM.user.id,sign: value});
        });
        //监听在线状态的切换事件
        layim.on('online', function (data) {
            socket.send(JSON.stringify({type: data}));
        });
        layim.on('chatChange', function(res){
            var type = res.data.type;
            if(type === 'friend'){
                layim.setChatStatus(res.data.sign);
            }
        });
        //监听自定义工具栏点击，以添加代码为例
        layim.on('tool(code)', function(insert){
            layer.prompt({
                title: '插入代码'
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
            console.log(res);
            // 离线消息
            $.post("/imApi/getOffLineMsg", {uid:IM.user.id}, function (data) {
                if (data.code == 0) {
                    var history_message=data.data;
                    for (var key in history_message) {
                        console.log(history_message[key]);
                        layui.layim.getMessage(history_message[key]);
                    }
                } else {
                    alert(data.msg);
                }
            }, 'json');
        });
    });
}