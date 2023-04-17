<?php


class ConsoleView
{
    public function printCryptoCurrencies($data)
    {
        echo "ID\tNAME\n\n";
        foreach ($data as $key => $value) {
            echo $value['code'] . "\t" . $value['name'] . "\n";
        }
    }

    public function printFiatCurrencies($data)
    {
        echo "ID\tNAME\n\n";
        foreach ($data as $key => $value) {
            echo $value['id'] . "\t" . $value['name'] . "\n";
        }
    }

    public function printExchangeRate($crypto, $fiat, $exchange_rate)
    {
        echo sprintf("%s = %s %s\n", $crypto, $exchange_rate, $fiat);
    }

    public function printAmountOfCoins($crypto, $fiat, $credit, $amount)
    {
        echo sprintf("%s %s = %s %s", $credit, $fiat, $amount, $crypto);
    }

    public function printHelpText()
    {
        echo "Help Text";
    }

    public function printErrorMessage($message) {
        echo $message . "\n";
    }
}
