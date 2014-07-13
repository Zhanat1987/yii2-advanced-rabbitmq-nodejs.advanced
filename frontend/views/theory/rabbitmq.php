<h1>
    RabbitMQ Теория
</h1>
<p>
    RabbitMQ - брокер сообщений, посредник, берущий на себя вопросы их маршрутизации и
    доставки одному или нескольким получателям, даже в случае сбоев оборудования и
    недоступности сетевого соединения.
</p>
<p>
    В основе RabbitMQ лежит протокол AMQP, который вводит три основных понятия:
    <br />
<ul>
    <li>Сообщение (message) - единица информации, которая передается от отправителя к получателю(ям);
        состоит из набора заголовков и содержания, которое брокером никак не интерпретируются.</li>
    <li>Точка обмена (exchange) - распределяет отправленные сообщения между одной или
        несколькими очередями в соответствии с их заголовками.</li>
    <li>
        Очередь (queue) - место, где хранятся сообщения до тех пор, пока их не заберет получатель.
    </li>
</ul>
</p>
<p>
    Базовые механизмы взаимодействия с брокером очень просты:
<br />
<ul>
    <li>Отправить сообщение (publish) — сообщение сериализуется в определенный формат,
        при необходимости снабжается маршрутной меткой (routing key) и передается в RabbitMQ;</li>
    <li>
        Получать сообщение (consume или subscribe) — приложение регистрируется в RabbitMQ
        с указанием какие именно сообщения оно готово получать и обрабатывать,
        после чего ожидает их доставки.
    </li>
</ul>
</p>
<pre>
    Перед началом любого взаимодействия с брокером клиент должен указать какая точка обмена
    должна заниматься обработкой его сообщений, что при необходимости её и зарегистрирует.
    При этом он указывает её название и тип, которых доступно три:
    - Отправка всем (fanout) — как следует из названия, каждое сообщение получат все очереди,
    связанные с данной точкой обмена, типичная публикация-подписка (publish-subscribe).
    - Прямая (direct) - сообщение получит только та очередь, которая имеет название,
    соответствующее маршрутной метке сообщения, типичная очередь сообщений (message queue).
    - Тематическая (topic) — очереди при регистрации указывают паттерн маршрутных меток сообщений,
    которые они хотели бы получать. Этот механизм позволяет наиболее гибко управлять маршрутизацией
    сообщений и строить нетривиальные схемы доставки. Вместо регулярных выражений используется
    очень простая схема: метки в виде слов, разделенных точками; в паттерне * заменяет ровно одно слово,
    # — ноль или больше; при отсутствии этих символов работает как прямая точка обмена.
</pre>
<pre>
    Выполнение длительных операций
    Представим себя интернет-проектом, который размещает у себя пользовательские видео или фото.
    Когда он получает по HTTP очередной файл, ему требуется сконвертировать его в стандартный формат
    для просмотра другими пользователями, а также, например, сделать несколько превью разного размера.
    По-старинке эти операции делают последовательно в том же обработчике запроса, который и принял
    от пользователя файл. В схеме с брокером же после принятия файла он отправляет сообщение,
    в содержании которого будет, вероятно, ссылка на файл оригинала, после чего он возвращает браузеру
    сообщение об успешной загрузке файла. Для отправки таких сообщений используют прямую точку обмена,
    с какой-то стандартной маршрутной меткой и соответствующим именем очереди,
    например process_video или create_thumbnails. Процессы, реализующие совершенно независимый сервис
    по выполнению этих длительных операций, будут по очереди забирать сообщения с «заданиями» из брокера,
    позволяя легко создавать любое количество исполнителей c балансировкой нагрузки,
    что обеспечит горизонтальное масштабирование этой подсистемы.
    Еще один доступный механизм, который вписывается в эту задачу — подтверждение о получении сообщения (acknowledgement).
    Получатель должен отправить брокеру дополнительное сообщение о том, что такое-то сообщение было успешно получено,
    в противном случае оно останется в очереди ожидать следующего получателя.
    Если процессы-исполнители будут подтверждать получение только после успешного выполнения длительной операции,
    это будет гарантировать, что все задания будут успешно выполнены вне зависимости от сбоев на каждом конкретном исполнителе,
    что обеспечивает отказоустойчивость.
</pre>
<pre>
    Удаленный вызов (RPC)
    Для некоторых приложений важно не только отправить запрос на выполнение какой-то операции,
    но и получить в ответ какой-то результат. На самом деле использование брокера сообщений
    в этой ситуации не всегда является удачным решением, проще делать это напрямую посредством
    других технологий. Но если в системе итак присутствует брокер, а для удаленного вызова нет
    строгих требований по времени выполнения, плюс хочется подобно предыдущему примеру легко
    получить отказоустойчивость и балансировку нагрузки, то можно реализовать удаленный вызов и через брокер сообщений.
    Для этого предусмотрено два заголовка сообщений:
    - Обратный адрес (reply to) — исполнитель должен отправить результат
    в очередь с указанным именем; отправитель сразу же после передачи сообщения-запроса
    брокеру начинает получать сообщения из указанной в этом заголовке очереди.
    - Идентификатор запроса (correlation id) — должен быть уникальным среди запросов,
    чтобы отправитель мог сопоставить результаты с запросами.
</pre>
<pre>
    Сообщения пользователям
    Очереди можно использовать как входящие почтовые ящики для пользователей веб-приложений.
    Какие-то компоненты системы или другие пользователи с использованием прямой точки обмена
    отправляют сообщения в очереди, содержащие в названии уникальный идентификатор пользователя-получателя.
    Там они ожидают пока он их не прочитает, например, зайдя на определенную страницу сайта.
    В этом примере очень важно использовать режим постоянных сообщений
    (persistant, путем установки заголовка delivery_mode=2), так как получатель сообщения может появиться
    очень не скоро и важно чтобы сообщения «переживали» даже полный перезапуск брокера сообщений.
    Для более короткоживущих сообщений это менее критично, но тоже порой актуально,
    особенно как еще одна мера для обеспечения отказоустойчивости.
    Пример хоть и немного оторванный от реальности из-за очистки почтового ящика после каждого прочтения,
    но в каких-то ситуациях все же может иметь право на существование.
</pre>
<pre>
    Двустороннее соединение с браузером
    Пожалуй, самый «вкусный» пример, хоть и лежащий на поверхности.
    На многих крупных интернет-проектах, особенно социальной направленности
    можно увидеть уведомления в реальном времени о событиях на сайте —
    кто-то что-то написал, поставил +1, проголосовал и т.п.
    Реализация этого функционала требует довольно серьезной работы как на стороне браузера,
    так и на серверной стороне. Браузерный вопрос выходит за рамки этой статьи,
    а вот на серверной стороне брокер сообщений окажется очень даже кстати, особенно в реализации RabbitMQ.
    На серверной части эта задача делится на две части:
    - Поддерживать  постоянное соединение со всеми пользователями,
    кто находится онлайн — здесь на помощь обычно приходит либо Erlang,
    либо неблокирующий сервер на epoll. Оба варианта очень неплохие, выбирайте сами.
    - Дальше нужно как-то организовать доставку сообщений (информацию о событиях в системе)
    между пользователями, где и вступает в игру брокер. Обработчик соединения подписывается
    на сообщения о публичных событиях (точка обмена «отправить всем»),
    и туда же отправляет информацию о действиях пользователя-владельца.
    Чем больше пользователей онлайн, тем больше сообщений в единицу времени будет проходить через брокер.
    Один сервер перестанет справляться довольно быстро, так что следующий раздел статьи окажется очень кстати.

</pre>
<pre>
    Кластеризация
    Многое из вышеизложенного справедливо и для других реализаций AMQP, но в вопросе кластеризации RabbitMQ предстает во всей красе. Залогом этого в первую очередь является использование Erlang, не знаю почему я до сих пор не написал статью про этот язык программирования, здесь достаточно было бы на нее сослаться и все стало бы ясно.
    Если вкратце, то в Erlang реализована внутренняя система легковесных процессов, не имеющая общего состояния и взаимодействующая друг с другом исключительно посредством обмена сообщений. При этом с точки разработчика отправка сообщений другому процессу на том же физическом сервером и на удаленном выглядит одинаково, и даже является одним из операторов языка — «!», наравне с «=», «+» и.т.п. Этот факт позволяет приложениям или их частям взаимодействовать по сети так же легко, как и в рамках одного сервера.
    Чтобы определить разрешено ли разным Erlang-сервера взаимодействовать друг с другом, они обмениваются хэшем пароля (который правда называют cookie, хотя с одноименным механизмом браузеров он ничего общего не имеет) и продолжают работу только если он совпал. Он должен быть одинаковым на всех узлах и хранится в файле ~/.erlang.cookie, для RabbitMQ это обычно  /var/lib/rabbitmq/.erlang.cookie — первым делом нужно решить этот вопрос, а также убедиться, что используется нестандартное значение.
    Узлы в RabbitMQ кластере могут быть двух типов: работающие только в памяти и сохраняющие данные на диск. Так как состояние системы реплицируется между узлами кластера, в большинстве случаев достаточно иметь лишь 2-3 дисковых узла, а остальные избавить от необходимости работать с дисковой подсистемой для увеличения производительности.
    Важно понимать, что под состоянием системы здесь имеются ввиду лишь привязки и настройки брокеров, каждая же очередь и хранящиеся в ней сообщения располагаются на одном конкретном узле, что приведет к потери части сообщений при сбое одного из серверов. Этот вопрос можно решить и средствами операционной системы, но чаще всего правильнее выделить критически-важные для системы очереди сообщений и включить их репликацию средствами RabbitMQ, этот механизм называется зеркальные очереди (mirrored queues).  Репликация происходит по принципу мастер-слуга (master-slave), как и в реляционных СУБД: все операции осуществляются на основном сервере (мастере), он транслирует их на один или несколько вторичных серверов (слуги), при каком-либо сбое на основном один из слуг «повышается» до статуса мастера и берет на себя его функции. Очереди могут быть объявлены зеркальными только при создании, но новые узлы в роли слуг могут добавляться и позже, в таком случае новый слуга начнет получать входящие сообщения и рано или поздно начнет полностью отражать его состояние, механизма синхронизации при подключении дополнительного слуги не предусмотрено. Последним шагом для гарантированной доставки сообщений, не упоминавшимся ранее, является механизм уведомления отправителя об успешной записи сообщения в очередь (на все сервера для зеркальных).
    В кластерном окружении может понадобиться объединение точек обмена (exchange federation), что реализуется посредством пересылки сообщений по однонаправленным связям. При этом учитывается наличие на принимающей стороне очередей, готовых принять каждое конкретное сообщение. Практического применения в веб-проектах этому пока особо не вижу, разве что при кросс-датацентровой работе. Кстати, для этого поддерживается работа поверх SSL.
    Для подключения узлов к кластеру можно использовать консольную утилиту (для временных изменений) или конфигурационные файлы (для постоянных настроек), подробно останавливаться не буду.

</pre>
<pre>
    Подводим итоги
    Используя брокер сообщений при технической реализации интернет-проекта,
    можно перевести его на совершенно новый уровень с точек зрения отказоустойчивости
    и горизонтальной масштабируемости. Во многих случаях он становится «сердцем» приложения,
    без которого его существование было бы немыслимо, но в то же время благодаря кластеризации
    не становится единственной точкой отказа (single point of failure).
    Хоть многое из упомянутого в статье можно реализовать и с помощью других технологий,
    RabbitMQ является наиболее приспособленной к реалиям современного Интернета реализацией
    брокера сообщений и AMQP в частности, в первую очередь благодаря распределенной природе Erlang
    и качественно спроектированной архитектуре этого продукта.
</pre>
<pre>
    Что касается реализации, то сначала осуществляем выгрузку изображения.
    Далее получаем информацию о нем, создаем новое сообщение с информацией пользователя
    и информацией об изображении и публикуем сообщение.
    После рассылаем уведомление друзьям, что появилось новое изображение.
    Менеджер, который присуждает очки, добавляет "звездочки" пользователю за использование изображений.
    Дальше – изменение размера изображения, добавление различных элементов.
</pre>
<pre>
    Как двигается сообщение в AMQP?
    В AMQP и RabbitMQ сообщения напрямую в очередь никогда не отправляются.
    Они всегда поступают сначала в брокер, потом - в обмен и далее - в Exchange.
    Там проверяются таблицы маршрутизации. Если получаются совпадения,
    тогда сообщение попадает в очередь. После этого либо мы от него отказываемся,
    либо оно направляется в Publisher и так далее.
    Сообщение может попадать в одну или много очередей.
    Получатель сообщения может быть один. Адресатов также могут быть сотни.
</pre>
<pre>
    Существует три типа обмена. Fanout – расширение. Direct – прямое. Topic – тематическое.
</pre>
<pre>
    Producer (поставщик) ? программа, отправляющая сообщения
    Queue (очередь) – буффер, хранящий сообщение
    Consumer (подписчик) ? программа, принимающая сообщения.
</pre>
<pre>
    Основная идея в модели отправки сообщений Rabbit – Поставщик(producer)
    никогда не отправляет сообщения напрямую в очередь. Фактически, довольно часто
    поставщик не знает, дошло ли его сообщение до конкретной очереди.
    Вместо этого поставщик отправляет сообщение в точку доступа.
    В точке доступа нет ничего сложного. Точка доступа выполняет две функции:
    — получает сообщения от поставщика
    — отправляет эти сообщения в очередь.
    Точка доступа точно знает, что делать с поступившими сообщениями.
    Отправить сообщение в конкретную очередь, либо в несколько очередей,
    либо не отправлять никому и удалить его. Эти правила описываются в типе точки доступа (exchange type).
</pre>
<pre>
    php + rabbitmq + nodejs + mongodb + percona
    Стоит ли использовать Mongo и Node.js для сервиса аналога Яндекс.Метрики и Google Analytics?
</pre>
<p>
    yii rabbitmq отправка рассылки с прикрепленными файлами и ресайз оригинала картинки, stomp js - ответ о выполнении задачи
</p>
<p>
    очистка очереди - как выполнил сообщение из очереди, сразу удалил его
</p>
<pre>
    отправка рассылки с прикрепленными файлами:
точка обмена direct - ?
рассылать письма порционно ~ по 100 за раз (но надо учесть тяжесть письма,
т.е. если например прикрепляется много файлов, то уменьшить кол-во) (для теста по 3 за раз)
Как вариант снижения нагрузки — может быть подготовка очереди рассылки и дальнейшая отправка писем из очереди порциями - т.е. при создании очереди надо брать всех подписчиков и в цикле собирать порции подписчиков - сообщения и отправлять их в прямую точку обмена, затем обрабатывать поочередно сооб-я из этой точки обмена и делать отчеты об успешных рассылках и не успешных в разные логи.
</pre>