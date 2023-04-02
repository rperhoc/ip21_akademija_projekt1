<?php

function listCryptoCurrencies($data)
{
    echo "ID\tNAME\n\n";
    foreach ($data as $key => $value) {
        echo $value['code'] . "\t" . $value['name'] . "\n";
    }
}

function listFiatCurrencies($data)
{
    echo "ID\tNAME\n\n";
    foreach ($data as $key => $value) {
        echo $value['id'] . "\t" . $value['name'] . "\n";
        }
}

function printExchangeRate($crypto, $fiat, $exchange_rate)
{
    echo sprintf("%s = %s %s\n", $crypto, $exchange_rate, $fiat);
}

function printAmountOfCoins($crypto, $fiat, $credit, $amount)
{
    echo sprintf("%s %s = %s %s", $credit, $fiat, $amount, $crypto);
}

function printHelpText()
{
    echo "Help Text";
}
