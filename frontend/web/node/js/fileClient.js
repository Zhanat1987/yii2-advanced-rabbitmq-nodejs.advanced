window.onload = function() {
    var socket = io.connect('http://localhost:8080');
    socket.on('file', function(data) {
        var file = '<p>Имя файла: ' + data.name + '</p><p><img src="' + data.widthThumb +
            '" /></p><p><img src="' + data.heightThumb + '" /></p>';
        document.querySelector('.files').innerHTML =
            document.querySelector('.files').innerHTML + file;
    });
}