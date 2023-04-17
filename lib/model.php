<?php

class Model
{
    private $fiatCurrencies = null;
    private $cryptoCurrencies = null;

    public function validateInputArgs($args)
    {
        for ($i = 1; $i < sizeof($args); $i++) {
            if ( (strlen($args[$i]) > 20) || (strlen($args[$i]) < 3) ) {
                return false;
            }
        }
        return true;
    }

    private function getApiData($api_endpoint)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CAINFO, 'C:\Users\roman\Downloads\cacert.pem');
        curl_close($ch);
        $data = json_decode(curl_exec($ch), true);

        if (isset( $data['errors']) || $data === null ) {
            $error_message = "Error(s) retrieving data from API endpoint:\n";
            foreach ($data['errors'] as $key => $value) {
                $error_message .= "$value\n";
            }
            throw new Exception($error_message);
        }
        return $data['data'];
    }

    public function getFiatData()
    {
        if ($this->fiatCurrencies == null) {
            $endpoint = 'https://api.coinbase.com/v2/currencies';
            $api_data = $this->getApiData($endpoint);
            if ( !is_string($api_data) ) {
                $this->fiatCurrencies = $api_data;
                return $this->fiatCurrencies;
            } else {
                return $api_data;
            }
        }
        return $this->fiatCurrencies;
    }

    public function getCryptoData()
    {
        if ($this->cryptoCurrencies == null) {
            $endpoint = 'https://api.coinbase.com/v2/currencies/crypto';
            $api_data = $this->getApiData($endpoint);
            if ( !is_string($api_data) ) {
                $this->cryptoCurrencies = $api_data;
                return $this->cryptoCurrencies;
            } else {
                return $api_data;
            }
        }
        return $this->cryptoCurrencies;
    }

    public function isFiatListed($fiat)
    {
        $fiat = strtoupper($fiat);
        $fiatData = $this->getFiatData();
        foreach ($fiatData as $key => $value) {
            if ($fiat == $value['id']) {
                return true;
            }
        }
        return false;
    }

    public function isCryptoListed($crypto)
    {
        $crypto = strtoupper($crypto);
        $cryptoData = $this->getCryptoData();
        foreach ($cryptoData as $key => $value) {
            if ($crypto == $value['code']) {
                return true;
            }
        }
        return false;
    }

    public function getRateEndpoint($crypto, $fiat, $price = 'spot')
    {
        return sprintf("https://api.coinbase.com/v2/prices/%s-%s/%s", $crypto, $fiat, $price);
    }

    public function getExchangeRate($crypto, $fiat, $price = 'spot')
    {
        $api_endpoint = $this->getRateEndpoint($crypto, $fiat);
        return $this->getApiData($api_endpoint)['amount'];
    }

    public function calculateAmountOfCoins($credit, $exchange_rate)
    {
        return $credit / $exchange_rate;
    }
}
