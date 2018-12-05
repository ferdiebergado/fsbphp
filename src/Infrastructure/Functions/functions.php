<?php

namespace Bergado\Infrastructure\Functions;

/*** HELPER FUNCTIONS ***/

/* Sanitize a request/response variable */
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/* Sanitize the superglobals */
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

/* Human readable date differences */
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

/* Get ordinal form of a number */
function ordinal($cdnl)
{
    $test_c = abs($cdnl) % 10;
    $ext = ((abs($cdnl) % 100 < 21 && abs($cdnl) % 100 > 4) ? 'th'
        : (($test_c < 4) ? ($test_c < 3) ? ($test_c < 2) ? ($test_c < 1)
        ? 'th' : 'st' : 'nd' : 'rd' : 'th'));
    return $cdnl . $ext;
}

/* Read contents of a csv file */
function readcsv($csvFile)
{
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle)) {
        $line_of_text[] = fgetcsv($file_handle, 1024);
    }
    fclose($file_handle);
    return $line_of_text;
}

/* Generate a csv file */
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

/* Encode an email away from spambots */
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

function jsonize(array $data)
{
    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

function convert($size)
{
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
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
