<?php


class ConsoleView
{
    public function printCryptoCurrencies(array $data): void
    {
        echo "ID\tNAME\n\n";
        foreach ($data as $key => $value) {
            echo $value['code'] . "\t" . $value['name'] . "\n";
        }
    }

    public function printFiatCurrencies(array $data): void
    {
        echo "ID\tNAME\n\n";
        foreach ($data as $key => $value) {
            echo $value['id'] . "\t" . $value['name'] . "\n";
        }
    }

    public function printExchangeRate(string $crypto, string $fiat, string|float $exchange_rate): void
    {
        echo sprintf("%s = %s %s\n", $crypto, $exchange_rate, $fiat);
    }

    public function printAmountOfCoins(string $crypto, string $fiat, string|float $credit, string|float $amount): void
    {
        echo sprintf("%s %s = %s %s", $credit, $fiat, $amount, $crypto);
    }

    public function printHelpText(): void
    {
        echo "Help Text";
    }

    public function printErrorMessage(string $message): void
    {
        echo $message . "\n";
    }
}
