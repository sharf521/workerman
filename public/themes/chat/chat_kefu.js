IM.initKeFu=false;
IM.showChat=function (id) {
    IM.serviceList.forEach(function (k) {
        if(k.id==id){
            layui.layim.chat(k);
            return;
        }
    });
};
function openKeFu(id) {
    if(IM.initKeFu){
        IM.showChat(id);
        return;
    }
    IM.showChat_id=id;
    IM_initUser();
}
function IM_initUser()
{
    IM.initKeFu=true;
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
}

IM.setFriendStatus=function(id,status){
    if (status == 'online') {
        $('.laykefu' + id).removeClass('layim-list-gray');
        layui.layim.setFriendStatus(id, 'online');
    } else if (status == 'offline') {
        $('.laykefu' + id).addClass('layim-list-gray');
        layui.layim.setFriendStatus(id, 'offline');
    }
};
IM.inited = false;
function connect_workerman() {
    socket = new WebSocket(IM.ws+'/?token='+IM.user.token);
    socket.onopen = function () {
        var initStr = IM.user;
        initStr['type'] = 'init';
        initStr['serviceIds']=[];
        IM.serviceList.forEach(function (k) {
            initStr['serviceIds'].push(k.id);
        });
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
                IM.setFriendStatus(msg.data.id,'online');
                return;
            case 'chatMessage':
                if (IM.user.id !== msg.data.id) {
                    layui.layim.getMessage(msg.data);
                }
                return;
            case 'hide':
            case 'logout':
                IM.setFriendStatus(msg.id,'offline');
                return;
            case 'online':
                IM.setFriendStatus(msg.id,'online');
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
    layui.use('layim', function (layim) {
        layim=layui.layim;
        console.log(layim);
        //基础配置
        layim.config({
            init: {
                //配置客户信息
                mine: {
                    "username": IM.user.username //我的昵称
                    ,"id": IM.user.id //我的ID
                    ,"status": "online" //在线状态 online：在线、hide：隐身
                    ,"sign": "在深邃的编码世界，做一枚轻盈的纸飞机" //我的签名
                    ,"avatar":IM.user.avatar
                }
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
            //开启客服模式
            ,min:true
            ,brief: false
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
        if(IM.showChat_id){
            IM.showChat(IM.showChat_id);
        }
        IM.serviceList.forEach(function (v) {
            if(IM.online_list.includes(v.id)){
                IM.setFriendStatus(v.id,'online');
            }else{
                IM.setFriendStatus(v.id,'offline');
            }
        });
    });
}