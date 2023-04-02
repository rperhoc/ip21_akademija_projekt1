<?php

function validateInputArgs($args)
{
    for ($i = 1; $i < sizeof($args); $i++) {
        if ( (strlen($args[$i]) > 10) || (strlen($args[$i]) < 3) ) {
            return false;
        }
    }
    return true;
}

function getApiData($api_endpoint)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CAINFO, 'C:\Users\roman\Downloads\cacert.pem');
    curl_close($ch);
    $data = json_decode(curl_exec($ch), true);

    if (isset( $data['errors']) || $data === null ) {
        return false;
        foreach ($data['errors'] as $key => $value) {
            return false;
        }
    } else {
        return $data['data'];
    }
}

function getFiatData()
{
    $endpoint = 'https://api.coinbase.com/v2/currencies';
    return getApiData($endpoint);
}

function getCryptoData()
{
    $endpoint = 'https://api.coinbase.com/v2/currencies/crypto';
    return getApiData($endpoint);
}

function fiatListed($fiat, $fiatData)
{
    foreach ($fiatData as $key => $value) {
        if ($fiat == $value['id']) {
            return true;
        }
    }
    return false;
}

function cryptoListed($crypto, $cryptoData)
{
    foreach ($cryptoData as $key => $value) {
        if ($crypto == $value['code']) {
            return true;
        }
    }
    return false;
}

function getRateEndpoint($crypto, $fiat, $price = 'spot')
{
    return sprintf("https://api.coinbase.com/v2/prices/%s-%s/%s", $crypto, $fiat, $price);
}

function getExchangeRate($crypto, $fiat, $price = 'spot')
{
    $api_endpoint = getRateEndpoint($crypto, $fiat);
    return getApiData($api_endpoint)['amount'];
}

function calculateAmountOfCoins($credit, $exchange_rate)
{
    return $credit / $exchange_rate;
}
