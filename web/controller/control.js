(function () {

    // if is mobile then redirect to controller page
    if (isMobile()) {
        window.location = '/controller/';
    }

    var ws = new WebSocket('ws://' + location.hostname + ":2346");
    // var ws = new WebSocket('ws://localhost:2346');
    ws.onopen = function () {
        ws.send('i am ppt');
    };
    ws.onmessage = function (e) {
        console.log('ppt get message from server: ' + e.data);
        switch (e.data) {
            case 'up':
                Reveal.up();
                break;
            case 'right':
                Reveal.right();
                break;
            case 'down':
                Reveal.down();
                break;
            case 'left':
                Reveal.left();
                break;
            default:
                console.log('unSupport command : ' . e.data)
        }
    };

    // 判断浏览器函数
    function isMobile() {
        return window.navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i);
    }

}())