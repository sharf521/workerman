inited = false;
function connect_workerman() {
    socket = new WebSocket('ws://127.0.0.1:7272/?token='+IM.user.token);
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
                IM.online_list=msg.online_list;
                return;
            case 'addList':
                if (IM.user.id != msg.data.id) {
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
            case 'logout':
                layui.mobile.layim.setFriendStatus(msg.id, 'offline'); //设置指定好友在线，即头像置灰
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
function initim(history_message) {
    if (inited) {
        // 离线消息
        for (var key in history_message) {
            layui.layim.getMessage(JSON.parse(history_message[key]));
        }
        return;
    }
    inited = true;
    layui.use('mobile', function () {
        var mobile = layui.mobile
            ,layim = mobile.layim
            ,layer = mobile.layer;
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
                    ,"id": "group101"
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
            , title: 'LayChat'
            //,isNewFriend: false //是否开启“新的朋友”
            ,isgroup: true //是否开启“群聊”
            //,chatTitleColor: '#c00' //顶部Bar颜色
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

        //查看聊天信息
        layim.on('detail', function(data){
            //console.log(data); //获取当前会话对象
            layim.panel({
                title: data.name + ' 聊天信息' //标题
                ,tpl: '<div style="padding: 10px;">自定义模版，<a href="http://www.layui.com/doc/modules/layim_mobile.html#ondetail" target="_blank">参考文档</a></div>' //模版
                ,data: { //数据
                    test: '么么哒'
                }
            });
        });

        //监听查看更多记录
        layim.on('chatlog', function(data, ul){
            console.log(data); //得到当前会话对象的基本信息
            console.log(ul); //得到当前聊天列表所在的ul容器，比如可以借助他来实现往上插入更多记录

            //弹出一个更多聊天记录面板
            layim.panel({
                title: '与 '+ data.username +' 的聊天记录' //标题
                ,tpl: '这里是模版，{{d.data.test}}' //模版
                ,data: { //数据
                    test: 'Hello'
                }
            });
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