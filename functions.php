<?php

function validateInputArgs($args)
{
    if ( (sizeof($args) != 2) && (sizeof($args) != 4) ) {
        echo "ERROR: Invalid number of arguments - See Help Text.\n";
        exit();
    } else {
        for ($i = 1; $i < sizeof($args); $i++) {
            if ( (strlen($args[$i]) > 10) || (strlen($args[$i]) < 3) ) {
                echo "ERROR: Length of arguments shall be between 3 and 10.";
                exit();
            }
        }
    }
}

function listCurrencies()
{
    $fiat_data = getFiatData();
    echo "ID\tNAME\n\n";
    foreach ($fiat_data as $key => $value) {
        echo $value['id'] . "\t" . $value['name'] . "\n";
    }

    $fiat_data = getFiatData();
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

    if (isset( $data['errors']) ) {
        echo "ERROR(s) trying to retrieve data from API endpoint: \n";
        foreach ($data['errors'] as $key => $value) {
            echo ($key + 1) . ": " . $value['message'] . "\n";
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

function getRateEndpoint($crypto, $fiat, $price = 'spot')
{
    return sprintf("https://api.coinbase.com/v2/prices/%s-%s/%s", $crypto, $fiat, $price);
}

function getExchangeRate($crypto, $fiat, $price = 'spot')
{
    if ( fiatListed($fiat) && cryptoListed($crypto) ) {
        $api_endpoint = getRateEndpoint($crypto, $fiat);
        return getApiData($api_endpoint)['amount'];
    } else {
        echo "Fiat and/or crypto currency you entered is not listed.\n";
        exit();
    }
}
