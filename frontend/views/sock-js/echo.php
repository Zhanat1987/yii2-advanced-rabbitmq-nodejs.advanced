<?php \frontend\assets\TempQueueAsset::register($this); ?>
<h1><a href="index.html">RabbitMQ Web STOMP Examples</a> > Temporary Queue</h1>

<p>When you type text in the form's input, the application will send a message to the <code>/queue/test</code> destination
    with the <code>reply-to</code> header set to <code>/temp-queue/foo</code>.</p>
<p>The STOMP client sets a default <code>onreceive</code> callback to receive messages from this temporary queue and display the message's text.</p>
<p>Finally, the client subscribes to the <code>/queue/test</code> destination. When it receives message from this destination, it reverses the message's
    text and reply by sending the reversed text to the destination defined by the message's <code>reply-to</code> header.</p>

<div id="first" class="box">
    <h2>Received</h2>
    <div></div>
    <form><input autocomplete="off" placeholder="Type here..."></input></form>
</div>

<div id="second" class="box">
    <h2>Logs</h2>
    <div></div>
</div>
<br />
<br />
<a href="https://github.com/rabbitmq/rabbitmq-web-stomp-examples/blob/master/priv/temp-queue.html">
    https://github.com/rabbitmq/rabbitmq-web-stomp-examples/blob/master/priv/temp-queue.html
</a>