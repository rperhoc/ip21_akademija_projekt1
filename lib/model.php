<?php

class Model
{
    public function validateInputArgs($args)
    {
        for ($i = 1; $i < sizeof($args); $i++) {
            if ( (strlen($args[$i]) > 10) || (strlen($args[$i]) < 3) ) {
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
            return false;
            foreach ($data['errors'] as $key => $value) {
                return false;
            }
        } else {
            return $data['data'];
        }
    }

    public function getFiatData()
    {
        $endpoint = 'https://api.coinbase.com/v2/currencies';
        return $this->getApiData($endpoint);
    }

    public function getCryptoData()
    {
        $endpoint = 'https://api.coinbase.com/v2/currencies/crypto';
        return $this->getApiData($endpoint);
    }

    public function fiatListed($fiat, $fiatData)
    {
        foreach ($fiatData as $key => $value) {
            if ($fiat == $value['id']) {
                return true;
            }
        }
        return false;
    }

    public function cryptoListed($crypto, $cryptoData)
    {
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
