<?php

function urlValid($url)
{
    $headers = get_headers($url);
    strpos($headers[0], '404') ? false : true;
}
