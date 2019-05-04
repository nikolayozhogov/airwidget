<? if (count($messages)): ?>

    <?php

    $_messages = $messages;

    // если не передана через ajax
    if (empty($last_day))
        $last_day = array_shift($_messages)['ts'];

    unset($_messages);
    ?>

    <? foreach ($messages as $message):

        $current_day = date('Y-m-d', $message['ts']);

        ?>

        <? if ($last_day !== $current_day):
        $last_day = $current_day;
        ?>
        <div class="message_day_separate" data-value="<?= $current_day ?>">
            <div class="message_day_label"><?= $current_day ?></div>
        </div>
    <? endif; ?>

        <div class="msg_before_separate"></div>

        <div class="message" data-id="<?= $message['_id'] ?>" data-ts="<?= $message['ts'] ?>">
            <div class="msg_inner">

                <div class="msg_user_photo">
                    <div class="pic">
                        <? if (!empty($message['user']['profile']['image_48'])): ?>
                            <img src="<?= $message['user']['profile']['image_48'] ?>" alt="">
                        <? endif; ?>
                    </div>
                </div>

                <div class="msg_content">

                    <div class="msg_info">

                        <? if (!empty($message['user']['real_name'])): ?>
                            <span class="msg_sender">
                                <?= $message['user']['real_name'] ?>
                            </span>
                        <? endif; ?>

                        <span class="msg_timestamp">
                            <?= date('H:i', $message['ts']) ?>
                        </span>

                    </div>

                    <? if (!empty($message['text'])): ?>
                        <div class="msg_text">
                            <?= $message['text'] ?>
                        </div>
                    <? endif; ?>

                    <? if (!empty($message['attachments'])): ?>
                        <div class="msg_attachments">
                            <? foreach ($message['attachments'] as $attachment): ?>

                                <div class="msg_attachment">
                                    <? if (!empty($attachment['service_icon'])): ?>
                                        <img src="<?= $attachment['service_icon'] ?>" alt="">
                                    <? endif; ?>

                                    <? if (!empty($attachment['title']) && !empty($attachment['original_url'])): ?>
                                        <a href="<?= $attachment['original_url'] ?>"
                                           target="_blank"><?= $attachment['title'] ?></a>
                                    <? endif; ?>
                                </div>

                            <? endforeach; ?>
                        </div>
                    <? endif; ?>

                    <? if (!empty($message['files'])): ?>
                        <div class="msg_files">
                            <? foreach ($message['files'] as $file):

                                $local_file_path = '/upload/' . $file['id'] . '.' . $file['filetype'];

                                ?>

                                <? if (file_exists(RD . $local_file_path)): ?>

                                    <?
                                    if (!in_array($file['filetype'], $enable_file_types))
                                        continue;
                                    ?>
                                    <div class="file">

                                        <a href="<?= $local_file_path ?>" target="_blank">
                                            <img src="<?= $local_file_path ?>" class="the_img" alt="">
                                        </a>

                                    </div>
                                <? else: ?>
                                    <div class="file">
                                        <a href="<?= $file['permalink'] ?>" target="_blank"><?= $file['name'] ?></a><br>
                                    </div>
                                <? endif; ?>
                            <? endforeach; ?>
                        </div>
                    <? endif; ?>

                    <? if (!empty($message['reactions'])): ?>
                        <div class="reactions">
                            <? foreach ($message['reactions'] as $reaction):
                                $reaction_filepath = '/assets/emoji/' . $reaction['name'] . '.png';
                                ?>
                                <? if (file_exists(RD . $reaction_filepath)): ?>
                                <div class="reaction">
                                    <img src="<?= $reaction_filepath ?>" alt=""
                                         title="<?= $reaction['count'] ?> people like this">
                                </div>
                            <? endif; ?>
                            <? endforeach; ?>
                        </div>
                    <? endif; ?>

                </div>
            </div>
        </div>
        <div class="msg_after_separate"></div>

        <? if (!empty($message['thread_ts']) && !empty($submessages[$message['thread_ts']])):
        $_submessages = $submessages[$message['thread_ts']];
        ?>

        <? foreach ($_submessages as $submessage): ?>

        <div class="msg_before_separate"></div>

        <div class="submessage" data-id="<?= $submessage['_id'] ?>" data-ts="<?= $submessage['ts'] ?>">
            <div class="submsg_inner">

                <div class="msg_user_photo">
                    <div class="pic">
                        <? if (!empty($submessage['user']['profile']['image_48'])): ?>
                            <img src="<?= $submessage['user']['profile']['image_48'] ?>" alt="">
                        <? endif; ?>
                    </div>
                </div>

                <div class="msg_content">

                    <div class="msg_info">

                        <? if (!empty($submessage['user']['real_name'])): ?>
                            <span class="msg_sender">
                                <?= $submessage['user']['real_name'] ?>
                            </span>
                        <? endif; ?>

                        <span class="msg_timestamp">
                            <?= date('H:i', $submessage['ts']) ?>
                        </span>

                    </div>

                    <? if (!empty($submessage['text'])): ?>
                        <div class="msg_text">
                            <?= $submessage['text'] ?>
                        </div>
                    <? endif; ?>

                    <? if (!empty($submessage['attachments'])): ?>
                        <div class="msg_attachments">
                            <? foreach ($submessage['attachments'] as $attachment): ?>

                                <div class="msg_attachment">
                                    <? if (!empty($attachment['service_icon'])): ?>
                                        <img src="<?= $attachment['service_icon'] ?>" alt="">
                                    <? endif; ?>

                                    <? if (!empty($attachment['title']) && !empty($attachment['original_url'])): ?>
                                        <a href="<?= $attachment['original_url'] ?>"
                                           target="_blank"><?= $attachment['title'] ?></a>
                                    <? endif; ?>
                                </div>

                            <? endforeach; ?>
                        </div>
                    <? endif; ?>

                    <? if (!empty($submessage['files'])): ?>
                        <div class="msg_files">
                            <? foreach ($submessage['files'] as $file):
                                $local_file_path = '/upload/' . $file['id'] . '.' . $file['filetype'];

                                ?>

                                <? if (file_exists(RD . $local_file_path)): ?>

                                    <?
                                    if (!in_array($file['filetype'], $enable_file_types))
                                        continue;
                                    ?>
                                    <div class="file">

                                        <a href="<?= $local_file_path ?>" target="_blank">
                                            <img src="<?= $local_file_path ?>" class="the_img" alt="">
                                        </a>

                                    </div>
                                <? else: ?>
                                    <div class="file">
                                        <a href="<?= $file['permalink'] ?>" target="_blank"><?= $file['name'] ?></a><br>
                                    </div>
                                <? endif; ?>
                            <? endforeach; ?>
                        </div>
                    <? endif; ?>

                    <? if (!empty($submessage['reactions'])): ?>
                        <div class="reactions">
                            <? foreach ($submessage['reactions'] as $reaction):
                                $reaction_filepath = '/assets/emoji/' . $reaction['name'] . '.png';
                                ?>
                                <? if (file_exists(RD . $reaction_filepath)): ?>
                                <div class="reaction">
                                    <img src="<?= $reaction_filepath ?>" alt=""
                                         title="<?= $reaction['count'] ?> people like this">
                                </div>
                            <? endif; ?>
                            <? endforeach; ?>
                        </div>
                    <? endif; ?>
                </div>
            </div>
        </div>
        <div class="msg_after_separate"></div>

    <? endforeach; ?>

    <? endif; ?>

    <? endforeach; ?>

<? endif; ?>