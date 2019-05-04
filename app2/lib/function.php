<?php

function enable_links($text)
{
    $matches = array();
    preg_match_all('/(http|https):\/\/([a-zA-Z0-9\@\-.\/?_=&]+)/u', $text, $matches);
    if (!empty($matches[0])) {
        foreach ($matches[0] as $match) {

            $link_text = $match;
            $link_text = str_replace('https://', '', $link_text);
            $link_text = str_replace('http://', '', $link_text);
            $link_text = str_replace('ftp://', '', $link_text);
            $link_text = str_replace('www.', '', $link_text);

            $link_tmp = explode('/', $link_text);
            if (count($link_tmp) > 1)
                $link_text = $link_tmp[0] . '/...';

            $text = str_replace($match, '<a href="' . $match . '" target="_blank">' . $link_text . '</a>', $text);
        }
    }

    return $text;
}


/*
 * Отправить уведомление в телеграм
 */

function sendNotifications($text)
{
    $url = 'https://api.telegram.org/' . TG_MOTIFY_BOT_API_KEY . '/sendmessage?chat_id=@' . TG_MOTIFY_CHAT_ID . '&text=' . $text;
    file_get_contents($url);
}

function sendNotification_actions($text)
{
    $url = 'https://api.telegram.org/' . TG_MOTIFY_BOT_API_KEY . '/sendmessage?chat_id=@' . TG_MOTIFY_CHAT_ID_ACTIONS . '&text=' . $text;
    file_get_contents($url);
}

/*
 * получить случаный цвет
 */
function getRandomColorHex()
{
    $r = rand(0, 180);
    $g = rand(0, 180);
    $b = rand(0, 180);
    $color = dechex($r) . dechex($g) . dechex($b);
    return "#" . $color;
}

/*
 * Не позволяет запустить по крону один и тот же файл дважды
 * Создается текстовый файл, который начинается с _lock_ и в него записывается id процесса
 */
function cli_lock_file()
{
    $lock_file_path = '/tmp/_lock_' . str_replace('.php', '', basename($_SERVER['PHP_SELF'])) . '.txt';
    $pid = 0;
    if (file_exists($lock_file_path))
        $pid = trim(file_get_contents($lock_file_path));
    if (file_exists("/proc/$pid"))
        exit('proc exists' . PHP_EOL);
    file_put_contents($lock_file_path, getmypid());
}

function getRussianMoth($month_id)
{
    $Month_r = array(
        "01" => "января",
        "02" => "февраля",
        "03" => "марта",
        "04" => "апреля",
        "05" => "мая",
        "06" => "июня",
        "07" => "июля",
        "08" => "августа",
        "09" => "сентября",
        "10" => "октября",
        "11" => "ноября",
        "12" => "декабря"
    );
    return $Month_r[$month_id];
}

function pre($s)
{
    echo '<pre>';
    print_r($s);
    echo '</pre>';
}

function declOfNum($number, $titles)
{
    $cases = array(2, 0, 1, 1, 1, 2);
    return $number . " " . $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
}

function curl_get($url)
{
    if ($curl = curl_init()) {

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        return array(
            'html' => curl_exec($curl),
            'info' => curl_getinfo($curl)
        );
    }
    return false;
}

function curl_post($url, $params)
{
    if ($curl = curl_init()) {

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

        return [
            'html' => curl_exec($curl),
            'info' => curl_getinfo($curl)
        ];
    }
    return false;
}
