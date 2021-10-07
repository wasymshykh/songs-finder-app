<?php

function normal_text($data)
{
    if (gettype($data) !== "array") {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    return '';
}

function normal_text_back($text)
{
    if (gettype($text) !== "array") {
        return trim(htmlspecialchars_decode(trim($text), ENT_QUOTES), ' ');
    }
    return '';
}

function normal_date($date, $format = 'M d, Y h:i A')
{
    $d = date_create($date);
    return date_format($d, $format);
}

function current_date($format = 'Y-m-d H:i:s')
{
    return date($format);
}

function normal_to_db_date($date, $format = 'Y-m-d H:i:s')
{
    $d = date_create($date);
    return date_format($d, $format);
}

function go ($URL)
{
    header("location: $URL");
    die();
}

function move ($PATH)
{
    header("location: ".URL."/$PATH");
    die();
}

function href ($PATH, $ADD_PHP = true)
{
    return URL."/$PATH" . ($ADD_PHP ? '.php' : '');
}

function css_link ($file, $tag = false)
{
    $link = URL.'/assets/css/'.$file.'.css';
    if ($tag) {
        return '<link rel="stylesheet" href="'.$link.'">';
    }
    return $link;
}

function js_link ($file, $tag = false)
{
    $link = URL.'/assets/js/'.$file.'.js';
    if ($tag) {
        return '<script src="'.$link.'"></script>';
    }
    return $link;
}

function end_response ($status_code, $message)
{
    http_response_code($status_code);
    echo json_encode(['status_code' => $status_code, 'message' => $message]);
    die();
}
