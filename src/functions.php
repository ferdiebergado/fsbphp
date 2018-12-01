<?php

/*** HELPER FUNCTIONS ***/

/* Fetch a config key */
if (!function_exists('config')) {
    function config($key)
    {
        $config = require(CONFIG_PATH . 'app.php');
        if (array_key_exists($key, $config)) {
            return $config[$key];
        }
    }
}

/* Fetch a config key */
// if (!function_exists('config')) {
//     function config($key)
//     {
//         $keys = explode('.', $key);
//         $config = require(CONFIG_PATH . $key[0] . '.php');
//         if (array_key_exists($key[1], $config)) {
//             return $config[$key];
//         }
//     }
// }

/* Sanitize a request/response variable */
if (!function_exists('test_input')) {
    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

/* Sanitize the superglobals */
if (!function_exists('sanitizeglobals')) {
    function sanitizeglobals()
    {
    //
    // Sanitize all dangerous PHP super globals.
    //
    // The FILTER_SANITIZE_STRING filter removes tags and remove or encode special
    // characters from a string.
    //
    // Possible options and flags:
    //
    //   FILTER_FLAG_NO_ENCODE_QUOTES - Do not encode quotes
    //   FILTER_FLAG_STRIP_LOW        - Remove characters with ASCII value < 32
    //   FILTER_FLAG_STRIP_HIGH       - Remove characters with ASCII value > 127
    //   FILTER_FLAG_ENCODE_LOW       - Encode characters with ASCII value < 32
    //   FILTER_FLAG_ENCODE_HIGH      - Encode characters with ASCII value > 127
    //   FILTER_FLAG_ENCODE_AMP       - Encode the "&" character to &amp;
    //
    //
    // <?php
    //
    // // Variable to check
    // $str = "<h1>Hello WorldÆØÅ!</h1>";
    //
    // // Remove HTML tags and all characters with ASCII value > 127
    // $newstr = filter_var($str, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    // echo $newstr;
    //  -> Hello World!

        foreach ($_GET as $key => $value) {
            $_GET[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
        }

        foreach ($_POST as $key => $value) {
            $_POST[$key] = test_input($_POST[$key]);
            $_POST[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
        }

        foreach ($_COOKIE as $key => $value) {
            $_COOKIE[$key] = filter_input(INPUT_COOKIE, $key, FILTER_SANITIZE_STRING);
        }

        foreach ($_SERVER as $key => $value) {
            $_SERVER[$key] = filter_input(INPUT_SERVER, $key, FILTER_SANITIZE_STRING);
        }

        foreach ($_ENV as $key => $value) {
            $_ENV[$key] = filter_input(INPUT_ENV, $key, FILTER_SANITIZE_STRING);
        }

        $_REQUEST = array_merge($_GET, $_POST);
    }
}

/* Set security headers */
if (!function_exists('set_secure_headers')) {
    function set_secure_headers()
    {
        $headers = require_once CONFIG_PATH . 'headers.php';
        foreach ($headers as $key => $value) {
            header("$key: $value", false);
        }
    }
}

/* Save an item into the cache */
if (!function_exists('cache_set')) {
    function cache_set($key, $expire = null, $val)
    {
        $path = cache_config('path');
        if (empty($expire)) {
            $expire = cache_config('expire');
        }
        $expire *= 60;
        $val = var_export($val, true);
       // HHVM fails at __set_state, so just use object cast for now
       // $val = str_replace('stdClass::__set_state', '(object)', $val);
       // Write to temp file first to ensure atomicity
        $tmp = $path . $key . uniqid('', true) . '.tmp';
        file_put_contents($tmp, '<?php $val = ' . $val . '; $exp = ' . $expire . ';', LOCK_EX);
        rename($tmp, $path . $key);
        return $val;
    }
}

/* Automatically get/set an item from/into the cache */
if (!function_exists('cache_remember')) {
    function cache_remember($key, $expire = null, $val)
    {
        if (cache_config('enabled')) {
            if (empty($expire)) {
                $expire = cache_config('expire');
            }
            $file = cache_config('path') . $key;
            if (!file_exists($file)) {
                $val = cache_set($key, $expire, $val);
            } else {
                @include $file;
            // Check file create time vs. your expire.
                if (filemtime($file) < (time() - $exp)) {
                    $val = cache_set($key, $exp, $val);
                }
            }
        }
        return $val;
    }
}

/* Flush an item from the cache */
if (!function_exists('cache_forget')) {
    function cache_forget($key)
    {
        $file = cache_config('path') . $key;
        if (file_exists($file)) {
            unlink($file);
        }
    }
}

/* Get a cache config variable */
if (!function_exists('cache_config')) {
    function cache_config($key)
    {
        $config = require(CONFIG_PATH . 'cache.php');
        $path = $config['path'];
        if (empty($path)) {
            $app = require(CONFIG_PATH . 'app.php');
            $path = sys_get_temp_dir() . '/' . $app['name'];
        }
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
        $config['path'] = $path . '/';
        return $config[$key];
    }
}

/* Log a message to the log file */
if (!function_exists('logger')) {
    function logger($msg, $type)
    {
        $mode = FILE_APPEND;
        if (!file_exists(LOG_FILE)) {
            $mode = LOCK_EX;
        }
        switch ($type) {
            case 1:
                $type = 'INFO';
                break;
            case 2:
                $type = 'ERROR EXCEPTION';
                break;
            case 3:
                $type = 'WARNING';
                break;
        }
        $log = '[' . date(DATE_FORMAT_LONG) . "] $type: " . $msg . "\n";
        file_put_contents(LOG_FILE, $log, $mode);
    }
}

/* Human readable date differences */
if (!function_exists('nicetime')) {
    function nicetime($date)
    {
        if (empty($date)) {
            return "No date provided";
        }

        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        $now = time();
        $unix_date = strtotime($date);

       // check validity of date
        if (empty($unix_date)) {
            return "Bad date";
        }

    // is it future date or past date
        if ($now > $unix_date) {
            $difference = $now - $unix_date;
            $tense = "ago";

        } else {
            $difference = $unix_date - $now;
            $tense = "from now";
        }

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if ($difference != 1) {
            $periods[$j] .= "s";
        }

        return "$difference $periods[$j] {$tense}";
    }
}

/* Get ordinal form of a number */
if (!function_exists('ordinal')) {
    function ordinal($cdnl)
    {
        $test_c = abs($cdnl) % 10;
        $ext = ((abs($cdnl) % 100 < 21 && abs($cdnl) % 100 > 4) ? 'th'
            : (($test_c < 4) ? ($test_c < 3) ? ($test_c < 2) ? ($test_c < 1)
            ? 'th' : 'st' : 'nd' : 'rd' : 'th'));
        return $cdnl . $ext;
    }
}

/* Read contents of a csv file */
if (!function_exists('readcsv')) {
    function readcsv($csvFile)
    {
        $file_handle = fopen($csvFile, 'r');
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 1024);
        }
        fclose($file_handle);
        return $line_of_text;
    }
}

/* Generate a csv file */
if (!function_exists('generatecsv')) {
    function generatecsv($data, $delimiter = ',', $enclosure = '"')
    {
        $handle = fopen('php://temp', 'r+');
        foreach ($data as $line) {
            fputcsv($handle, $line, $delimiter, $enclosure);
        }
        rewind($handle);
        while (!feof($handle)) {
            $contents .= fread($handle, 8192);
        }
        fclose($handle);
        return $contents;
    }
}

/* Encode an email away from spambots */
if (!function_exists('encode_email')) {
    function encode_email($email = 'info@domain.com', $linkText = 'Contact Us', $attrs = 'class="emailencoder"')
    {
        // remplazar aroba y puntos
        $email = str_replace('@', '&#64;', $email);
        $email = str_replace('.', '&#46;', $email);
        $email = str_split($email, 5);

        $linkText = str_replace('@', '&#64;', $linkText);
        $linkText = str_replace('.', '&#46;', $linkText);
        $linkText = str_split($linkText, 5);

        $part1 = '<a href="ma';
        $part2 = 'ilto&#58;';
        $part3 = '" ' . $attrs . ' >';
        $part4 = '</a>';

        $encoded = '<script type="text/javascript">';
        $encoded .= "document.write('$part1');";
        $encoded .= "document.write('$part2');";
        foreach ($email as $e) {
            $encoded .= "document.write('$e');";
        }
        $encoded .= "document.write('$part3');";
        foreach ($linkText as $l) {
            $encoded .= "document.write('$l');";
        }
        $encoded .= "document.write('$part4');";
        $encoded .= '</script>';

        return $encoded;
    }
}

if (!function_exists('jsonize')) {
    function jsonize(array $data)
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}

if (!function_exists('view')) {
    function view($view, $data, $template, $response)
    {
        $response = $response->createResponse();
        $template = $template->render($view, $data);
        $response->getBody()->write($template);
        return $response;
    }
}

if (!function_exists('convert')) {
    function convert($size)
    {
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }
}

/**
 * Gets the user ip address
 * @return string The user ip address
 */
if (!function_exists('getUserIp')) {
    function getUserIp($server)
    {
        if (isset($server['HTTP_CLIENT_IP'])) {
            $userIp = $server['HTTP_CLIENT_IP'];
        } else if (isset($server['HTTP_X_FORWARDED_FOR'])) {
            $userIp = $server['HTTP_X_FORWARDED_FOR'];
        } else if (isset($server['HTTP_X_FORWARDED'])) {
            $userIp = $server['HTTP_X_FORWARDED'];
        } else if (isset($server['HTTP_FORWARDED_FOR'])) {
            $userIp = $server['HTTP_FORWARDED_FOR'];
        } else if (isset($server['HTTP_FORWARDED'])) {
            $userIp = $server['HTTP_FORWARDED'];
        } else if (isset($server['REMOTE_ADDR'])) {
            $userIp = $server['REMOTE_ADDR'];
        } else {
            $userIp = 'UNKNOWN';
        }
        return $userIp;
    }
}

/**
 * Checks if user is a web crawler
 * @param string $userAgent
 * @param array $robots
 * @return bool True if user is a robot
 */
function isRobot($userAgent, array $robots = [])
{
    foreach ($robots as $robot) {
        if (strpos(strtolower($userAgent), $robot) !== false) {
            return true;
        }
    }

    return false;
}
