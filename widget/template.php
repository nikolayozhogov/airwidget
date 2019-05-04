<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slack widget <?= $peer['team_name'] ?></title>
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans+JP" rel="stylesheet">
    <link href="css/custom.css?v=1.1" rel="stylesheet" type="text/css">
    <script
            src="https://code.jquery.com/jquery-1.10.2.min.js"
            integrity="sha256-C6CB9UYIS9UJeqinPHWTHVqh/E1uhG5Twh+Y5qFQmYg="
            crossorigin="anonymous"></script>
    <script src="js/_script.js?v=<?= time() ?>"></script>
    <script>

        <?php

        $start_channel_id = '';
        if (count($channels)) {

            $_channels = $channels;
            $start_channel_id = (string)array_shift($_channels)['_id'];
            unset($_channels);

            if (!empty($peer['default_channel_id'])){
                $temp = (string)$peer['default_channel_id'];

                if (!empty($channels[$temp]))
                    $start_channel_id = $temp;
            }
        }

        ?>

        sw.peer_id = '<?=$peer['_id']?>';
        sw.start_channel_id = '<?=$start_channel_id?>'; // id канала, который нужно показать первым
        sw.channel_id = '<?=$start_channel_id?>'; // id текущего канала
    </script>

</head>
<body>

<div id="slack_widget">
    <div class="info_wrap">
        <div class="header_block_left">
            <div class="header_block_left_inner">
                <?= $peer['team_name'] ?>
            </div>
        </div>
        <div class="header_block_right">
            <div class="channel_info">
                <span class="channel_name"></span>
                <span class="channel_members"></span>
                <span class="channel_description"></span>
            </div>
        </div>
    </div>

    <div style="clear: both"></div>

    <div class="content_wrap">
        <div class="channels vertical_cont">
            <?php
            $count_channels = count($channels);
            $iterator = 0;
            ?>
            <? foreach ($channels as $i => $channel):

                $add_class = [];
                if ((string)$channel['_id'] == $start_channel_id)
                    $add_class[] = 'channel_active';

                $add_class = implode(' ', $add_class);

                $iterator++;
                ?>

                <div
                        class="channel change_channel <?= $add_class ?>"
                        <?if($count_channels == $iterator):?>style="margin-bottom: 34px;" <?endif;?>
                        data-id="<?= $channel['_id'] ?>">
                    # <?= $channel['name_normalized'] ?>
                </div>

            <? endforeach; ?>

            <div class="powered_by">powered by <a href="https://airwidget.app/?utm_source=widget&utm_medium=cpm&utm_campaign=id-<?=$peer['_id']?>" target="_blank">AirWidget</a></div>

        </div>
        <div class="messages vertical_cont">

            <?if (count($channels) == 0):?>

                <div class="message">
                    <div class="msg_inner">
                        <div class="msg_user_photo">
                            <div class="pic">
                                <img src="/assets/images/avatar.png" alt="">
                            </div>
                        </div>
                        <div class="msg_content">
                            <div class="msg_info"><span class="msg_sender">AirWidget</span>
                                <span class="msg_timestamp"><?= date('H:i', $peer['created_at']) ?></span>
                            </div>
                            <div class="msg_text">
                                <b>Hello, thank you for installing AirWidget!</b><br>
                                To customize the widget, use the command
                                <code class="slack_command"><b>/widget_commands</b></code> in your Slack workspace (in DM with @slackbot or in any public channel).
                            </div>

                        </div>
                    </div>
                </div>
            <?endif;?>

        </div>
    </div>
</div>
</body>
</html>