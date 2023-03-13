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

function getApiData($api_endpoint)
{
    $json = file_get_contents($api_endpoint);
    return json_decode($json, true)['data'];
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

function fiatListed($fiat)
{
    foreach (getFiatData() as $key => $value) {
        if (strtoupper($fiat) == $value['id']) {
            return true;
        }
    }
    return false;
}

function cryptoListed($crypto)
{
    foreach (getCryptoData() as $key => $value) {
        if (strtoupper($crypto) == $value['code']) {
            return true;
        }
    }
    return false;
}

function responseValid($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CAINFO, 'C:\Users\roman\Downloads\cacert.pem');
    curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($statusCode == 200);
}

function getRateEndpoint($crypto, $fiat, $price = 'spot')
{
    return sprintf("https://api.coinbase.com/v2/prices/%s-%s/%s", $crypto, $fiat, $price);
}

function getExchangeRate($crypto, $fiat, $price = 'spot')
{
    if ( fiatListed($fiat) && cryptoListed($crypto) ) {
        $api_endpoint = getRateEndpoint($crypto, $fiat);
        if ( responseValid($api_endpoint) ) {
            return getApiData($api_endpoint)['amount'];
        } else {
            echo $api_endpoint . "\n";
            echo "Could not retrieve data from API endpoint. Make sure you entered valid crypto and fiat currencies.\n";
            exit();
        }
    } else {
        echo "Fiat and/or crypto currency you entered is not listed.";
        exit();
    }
}
