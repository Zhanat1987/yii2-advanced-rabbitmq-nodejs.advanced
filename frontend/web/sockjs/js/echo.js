var has_had_focus = false;
var pipe = function(el_name, send) {
    var div = $(el_name + ' div');
    var inp = $(el_name + ' input');
    var form = $(el_name + ' form');

    var print = function(m, p) {
        p = (p === undefined) ? '' : JSON.stringify(p);
        div.append($("<code>").text(m + ' ' + p));
        div.scrollTop(div.scrollTop() + 10000);
    };

    if (send) {
        form.submit(function() {
            send(inp.val());
            inp.val('');
            return false;
        });
    }
    return print;
};

// Stomp.js boilerplate
var ws = new SockJS('http://' + window.location.hostname + ':15674/stomp');
var client = Stomp.over(ws);

// SockJS does not support heart-beat: disable heart-beats
client.heartbeat.outgoing = 0;
client.heartbeat.incoming = 0;
client.debug = pipe('#second');

var print_first = pipe('#first', function(data) {
    client.send('/topic/test', {"content-type":"text/plain"}, data);
});
var on_connect = function(x) {
    id = client.subscribe("/topic/test", function(d) {
        print_first(d.body);
    });
};
var on_error = function() {
    console.log('error');
};
client.connect('guest', 'guest', on_connect, on_error, '/');

$('#first input').focus(function() {
    if (!has_had_focus) {
        has_had_focus = true;
        $(this).val("");
    }
});