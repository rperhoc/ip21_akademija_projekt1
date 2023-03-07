<?php

function validateInputArgs($args)
{
    for ($i = 1; $i < sizeof($args); $i++) {
        if ( (strlen($args[$i]) > 10) || (strlen($args[$i]) < 3) ) {
            echo "ERROR: Length of arguments shall be between 3 and 10.";
            exit();
        }
    }
}

function responseValid($url)
{
    $headers = get_headers($url);
    return !strpos($headers[0], '404');
}

function getRateUrl($crypto, $fiat, $type = 'spot')
{
    return sprintf("https://api.coinbase.com/v2/prices/%s-%s/%s", $crypto, $fiat, $type);
}

function getApiData($api_url, $array = true)
{
    $json_file = file_get_contents($api_url);
    return json_decode($json_file, $array);
}

function fiatListed($fiat_currency, $fiat_list)
{
    foreach ($fiat_list['data'] as $key => $value) {
        if (strtoupper($fiat_currency) == $value['id']) {
            return true;
        }
    }
    return false;
}

function cryptoListed($crypto_currency, $crypto_list)
{
    foreach ($crypto_list['data'] as $key => $value) {
        if (strtoupper($crypto_currency) == $value['code']) {
            return true;
        }
    }
    return false;
}
