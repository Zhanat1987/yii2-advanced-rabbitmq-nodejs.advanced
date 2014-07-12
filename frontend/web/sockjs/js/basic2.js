Stomp.WebSocketClass = SockJS;

var client = Stomp.client('http://127.0.0.1:15674/stomp');
var on_connect = function() {
    console.log('connected');
};
var on_error =  function() {
    console.log('error');
};
client.connect('guest', 'guest', on_connect, on_error, '/');