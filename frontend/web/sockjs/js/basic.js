jQuery.fn.live = function (types, data, fn) {
    jQuery(this.context).on(types,this.selector,data,fn);
    return this;
};
jQuery(document).ready(function () {
    basicStart();
    basicStop();
});
function basicStart()
{
    $('.basicStart').bind('click', function() {
        $.ajax({
            type: 'POST',
            url: '/basic/start',
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
    });
}
function basicStop()
{
    $('.basicStop').bind('click', function() {
        $.ajax({
            type: 'POST',
            url: '/basic/stop',
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
    });
}