<?php

// system
define('EMAIL_REGEXP', "/^([a-zA-Z0-9_\-.]+)@([a-zA-Z0-9\-]+)\.([a-zA-Z0-9\-.])+$/");
define('INTERNAL_ERROR', 'Internal error, please try again later.');

// mongo
define('DB', 'slackwidget');
define('CL_PEER', 'peer'); // виджеты 
define('CL_USER', 'users'); // пользователи
define('CL_NOTIFY', 'notify'); // уведомления
define('CL_CHANNEL', 'channel'); // каналы (в пределах одного виджета)
define('CL_MESSAGE', 'message'); // сообщения из каналов
define('CL_BOT_MESSAGE', 'bot_message'); // сообщения которые бот отправляет
define('CL_SLACK_USER', 'slack_user'); // пользователи слака
define('CL_REACTION', 'reaction'); // смайлы
define('CL_LOG_EVENT', 'log_event'); // лог событий
define('CL_LOG_COMMAND', 'log_command'); // лог команд
define('CL_FEEDBACK', 'feedback'); // обратная связь

// slack
//define('SLACK_APP_ID', 'AD0ULEY4U');
define('SLACK_CLIENT_ID', '');
define('SLACK_CLIENT_ID_COPY', '');

define('SLACK_CLIENT_SECRET', '');
define('SLACK_CLIENT_SECRET_COPY', '');
//define('SLACK_VERIFICATION_TOKEN', '');

define('OAUTH_ACCESS_TOKEN', '');

// bot
define('BOT_USERNAME', 'slackwidget');
define('WELCOME_MESSAGE', "Hello, thank you for installing AirWidget! To customize the widget, use the following commands:\n");
define('SLASH_COMMANDS', "> *Share all public channels in widget*
> `/share_all`

> *Share specified channel in widget*
> `/share`
> Example: `/share #general`

> *Stop sharing specified channel in widget *
> `/unshare`
> Example: `/unshare #random`

> *Stop sharing all channels in widget *
> `/unshare_all`

> *Visible specified channel after page load*
> `/default`
> Example: `/default #dev`

> *Get html code of widget*
> `/widget_code`

> *Show AirWidget commands*
> `/widget_commands`

> *If you need more help you can always reach us with*
> `/widget_feedback`
> Provide your feedback as part of the command. For example: `/widget_feedback I wish AirWidget could do the dishes`");


define('TG_NOTIFY_BOT_KEY', 'bot...:..');
define('TG_NOTIFY_GROUP_ID', '');