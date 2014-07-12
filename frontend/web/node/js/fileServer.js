var context = require('rabbit.js').createContext(),
    io = require('socket.io').listen(8080),
    sub = context.socket('SUB');
sub.setEncoding('utf8');
io.sockets.on('connection', function(socket) {
    sub.connect('files');
    sub.on('data', function(data) {
        var message = JSON.parse(data);
        socket.emit(message.type, message.data);
    });
});