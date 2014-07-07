jQuery.fn.live = function (types, data, fn) {
    jQuery(this.context).on(types,this.selector,data,fn);
    return this;
};
jQuery(document).ready(function () {
    chat();
    chatStop();
});
function chat()
{
    $('.chatButton').bind('click', function() {
        if (validChatForm()) {
            $.ajax({
                type: 'POST',
//                type: 'GET',
//                url: '/chat/start',
                url: '/chat/start/' + $('.chatName').val() + '/' + $('.chatMessage').val(),
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
//                data: {
//                    'name' : $('.chatName').val(),
//                    'message' : $('.chatMessage').val(),
//                    '_csrf': yii.getCsrfToken() // yii.getCsrfParam() - возвращает '_csrf'
//                },
//                data: $('form').serialize(),
                success: function(response) {
                    if (response.status == 'ok') {
                        chatFormClear();
                        console.info(response.msg);
                    } else {
                        console.log(response.msg);
                    }
                }
            });
        }
        return false;
    });
}
function validChatForm()
{
    var valid = true;
    var $name = $('.chatName');
    var $message = $('.chatMessage');
    var name = $name.val();
    var message = $message.val();
    if (name) {
        $name.css({'border-color':'#ccc'});
    } else {
        $name.css({'border-color':'#A94442'});
        valid = false;
    }
    if (message) {
        $message.css({'border-color':'#ccc'});
    } else {
        $message.css({'border-color':'#A94442'});
        valid = false;
    }
    return valid;
}
function chatFormClear()
{
    var $name = $('.chatName');
    var $message = $('.chatMessage');
    $name.val($name.attr('username'));
    $message.val('');
}
function chatStop()
{
    $('.chatStop').bind('click', function() {
        $.ajax({
            type: 'POST',
            url: '/chat/stop',
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(response) {
                if (response.status == 'ok') {
                    console.info(response.msg);
                } else {
                    console.log(response.msg);
                }
            }
        });
        return false;
    });
}