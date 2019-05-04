$(function () {

    sw.render();

    // показываем канал
    if (sw.start_channel_id != null) {
        // получаем ленту сообщений
        sw.change_channel(sw.start_channel_id);
        // получаем название и описание канала
        sw.get_channel_info(sw.start_channel_id);
    }


    // проверяем наличие новых сообщений в текущем канале
    setTimeout(function () {
        sw.get_new_messages();
    }, 5000);

    // рендер
    $(window).on('resize', function () {
        sw.render()
    })

    // вешаем обработчик на получение старых сообщений
    $('.messages').on('scroll', function () {
        if (
            $(this).scrollTop() == 0
            && sw.lock_get_history == false
            && $('.message').size() > 0
        ) {
            sw.get_history();
        }
    })

    // обработчик на смену канала
    $('.change_channel').click(function () {

        $('.change_channel').removeClass('channel_active');
        $(this).addClass('channel_active');

        sw.lock_get_history = false;

        var channel_id = $(this).attr('data-id');
        // получаем ленту сообщений
        sw.change_channel(channel_id);
        // получаем название и описание канала
        sw.get_channel_info(channel_id);
    })
})

sw = {

    peer_id: null,
    channel_id: null,
    start_channel_id: null,
    debug: false,
    lock_get_history: false,
    ajax_process: '<div class="messages_loader"><img src="images/loading.gif" /></div>',

    render: function () {

        $('.vertical_cont').css({
            'height': $(window).height() - $('.header_block_right').outerHeight()
        });
        if ($('.channel').size()) {

            var a = $('.channels').outerHeight() - $('.powered_by').outerHeight() - 30,
                b = $('.channel').size() * $('.channel:eq(0)').outerHeight();

            if (a < b) {
                $('.channels').css({
                    'overflow-y': 'scroll'
                })
            } else {
                $('.channels').css({
                    'overflow-y': 'hidden'
                })
            }
        }

        $('.powered_by').css({
            'width': $('.channel').width() + 6
        })
    },

    // подгрузить историю сообщений
    get_history: function () {

        if ($('.message').size()) {

            sw.lock_get_history = true;
            if (sw.debug)
                console.log('lock');

            $('.messages').prepend(sw.ajax_process);

            start_ts = $(".message:eq(0)").attr('data-ts');

            if (sw.debug)
                console.log('get history, channel_id: ' + sw.channel_id + ', start_ts: ' + start_ts);

            $.ajax({
                url: "/widget/",
                type: 'POST',
                cache: false,
                data: {
                    id: sw.peer_id,
                    channel_id: sw.channel_id,
                    ts: start_ts,
                    type: 'history'
                },
                success: function (data) {
                    $('.messages_loader').remove();
                    $('.messages').prepend(data);
                    sw.lock_get_history = false;
                },
                error: function () {
                    $('.messages_loader').remove();
                    sw.lock_get_history = false;
                }
            })
        }
    },

    // проверка наличия новых сообщений
    get_new_messages: function () {

        if (sw.channel_id == '')
            return;

        // последнее сообщение
        var last_ts = 0;
        if ($('.message').size()) {
            last_ts = $(".message:eq(" + ($('.message').size() - 1) + ")").attr('data-ts');
        }

        // последняя временная отметка в сообщениях
        var last_day = '';
        if ($('.message').size()) {
            last_day = $(".message_day_separate:eq(" + ($('.message_day_separate').size() - 1) + ")").attr('data-value');
        }

        if (sw.debug)
            console.log('get new messages, channel_id: ' + sw.channel_id + ', last_ts: ' + last_ts);

        $.ajax({
            url: "/widget/",
            type: 'POST',
            cache: false,
            data: {
                id: sw.peer_id,
                channel_id: sw.channel_id,
                ts: last_ts,
                last_day: last_day,
                type: 'new'
            },
            success: function (data) {

                $('.messages').append(data);

                // удалим первый script
                if ($('.get_new_message').size() > 1)
                    $('.get_new_message:eq(0)').remove();
            },
            error: function () {
                //$('.messages_loader').hide();
            }
        })
    },

    // выбор другого канала
    // загрузка последних сообщений
    change_channel: function (channel_id) {

        if (channel_id == '')
            return;

        sw.channel_id = channel_id;

        $('.messages').html(sw.ajax_process);

        if (sw.debug)
            console.log('change channel, id: ' + channel_id);

        $.ajax({
            url: "/widget/",
            type: 'POST',
            cache: false,
            data: {
                id: sw.peer_id,
                channel_id: channel_id,
                type: 'latest'
            },
            success: function (data) {
                $('.messages').html(data);

                // scroll в низ
                sw.scroll_to_last_message();
            },
            error: function () {
                $('.messages_loader').remove();
            }
        })
    },

    // получить информацию о канале
    get_channel_info: function (channel_id) {

        if (sw.debug)
            console.log('get channel info, id: ' + channel_id);

        if (channel_id == '')
            return;

        $.ajax({
            url: '/widget/action/',
            type: 'POST',
            cache: false,
            dataType: 'json',
            data: {
                id: sw.peer_id,
                channel_id: channel_id,
                action: 'get_channel_info'
            },
            success: function (data) {

                $('.channel_name').html('#' + data.name);
                $('.channel_description').html(data.descr);
                $('.channel_members').html('(' + parseInt(data.members) + ')');
            }
        });
    },

    // scroll к самому последнему сообщению
    scroll_to_last_message: function () {
        $('.messages').scrollTop($('.messages').prop('scrollHeight'));

        // scroll вниз после загрузки всех картинок
        $('img').one('load', function () {
            $('.messages').scrollTop($('.messages').prop('scrollHeight'));
        })
    }
}