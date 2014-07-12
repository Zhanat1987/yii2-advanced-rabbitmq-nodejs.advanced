jQuery.fn.live = function (types, data, fn) {
    jQuery(this.context).on(types,this.selector,data,fn);
    return this;
};
jQuery(document).ready(function () {
    fileStart();
    fileStop();
});
function fileStart()
{
    $('.fileStart').bind('click', function() {
        $.ajax({
            type: 'POST',
            url: '/file/start',
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(response) {
                if (response.status == 'ok') {
                    $('.success').css({'color':'green'});
                } else {
                    $('.success').css({'color':'#f00'});
                }
                $('.success').text(response.msg);
            }
        });
        return false;
    });
}
function fileStop()
{
    $('.fileStop').bind('click', function() {
        $.ajax({
            type: 'POST',
            url: '/file/stop',
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(response) {
                if (response.status == 'ok') {
                    $('.success').css({'color':'green'});
                } else {
                    $('.success').css({'color':'#f00'});
                }
                $('.success').text(response.msg);
            }
        });
        return false;
    });
}