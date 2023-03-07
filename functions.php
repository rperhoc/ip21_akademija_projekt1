<?php

function argLenValid($arg, $limit)
{
    if (strlen($arg) > $limit) {
        return false;
    } else {
        return true;
    }
}

function urlValid($url)
{
    $headers = get_headers($url);
    strpos($headers[0], '404') ? false : true;
}
