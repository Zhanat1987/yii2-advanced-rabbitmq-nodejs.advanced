window.onload = function()
{
    var socket = io.connect('http://localhost:8080');
    socket.on('chat', function(data) {
        var message = '<p>Имя: ' + data.name + '<br />Сообщение: ' +
            data.message + '<br />Дата и время: ' + data.dateTime;
        document.querySelector('.chatDiv').innerHTML =
            document.querySelector('.chatDiv').innerHTML + message;
    });
}