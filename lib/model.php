<?php

class Model
{
    private const API_BASE = 'https://api.coinbase.com/v2/';
    private $fiatCurrencies = null;
    private $cryptoCurrencies = null;

    public function validateInputArgs(array $args): bool
    {
        for ($i = 1; $i < sizeof($args); $i++) {
            if ( (strlen($args[$i]) > 20) || (strlen($args[$i]) < 3) ) {
                return false;
            }
        }
        return true;
    }

    public function getFiatData(): array
    {
        if ($this->fiatCurrencies == null) {
            $endpoint = self::API_BASE . 'currencies';
            try {
                $api_data = $this->getApiData($endpoint);
                $this->fiatCurrencies = $api_data;
                return $this->fiatCurrencies;
            } catch (Exception $e) {
                throw new Exception( $e->getMessage() );
            }
        }
        return $this->fiatCurrencies;
    }

    public function getCryptoData(): array
    {
        if ($this->cryptoCurrencies == null) {
            $endpoint = self::API_BASE . 'currencies/crypto';
            try {
                $api_data = $this->getApiData($endpoint);
                $this->cryptoCurrencies = $api_data;
                return $this->cryptoCurrencies;
            } catch (Exception $e) {
                throw new Exception ( $e->getMessage() );
            }
        }
        return $this->cryptoCurrencies;
    }

    public function isFiatListed(string $fiat): bool
    {
        $fiat = strtoupper($fiat);
        try {
            $fiatData = $this->getFiatData();
        } catch (Exception $e) {
            throw new Exception( "Error in isFiatListed " . $e->getMessage() );
        }
        foreach ($fiatData as $key => $value) {
            if ($fiat == $value['id']) {
                return true;
            }
        }
        return false;
    }

    public function isCryptoListed(string $crypto): bool
    {
        $crypto = strtoupper($crypto);
        try {
            $cryptoData = $this->getCryptoData();
        } catch (Exception $e) {
            throw new Exception( "Error in isCryptoListed: " . $e->getMessage() );
        }
        foreach ($cryptoData as $key => $value) {
            if ($crypto == $value['code']) {
                return true;
            }
        }
        return false;
    }

    public function getRateEndpoint(string $crypto, string $fiat, string $price = 'spot'): string
    {
        return sprintf(self::API_BASE . "/prices/%s-%s/%s", $crypto, $fiat, $price);
    }

    public function getExchangeRate(string $crypto, string $fiat, string $price = 'spot'): string
    {
        try {
            $is_fiat_listed = $this->isFiatListed($fiat);
            $is_crypto_listed = $this->isCryptoListed($crypto);
        } catch (Exception $e) {
            throw new Exception ( "Error checking whether currencies are listed: " . $e->getMessage() );
        }
        if ($is_fiat_listed && $is_crypto_listed) {
            try {
                $api_endpoint = $this->getRateEndpoint($crypto, $fiat);
                return $this->getApiData($api_endpoint)['amount'];
            } catch (Exception $e) {
                throw new Exception ( "Error retrieving exchange rate: " . $e->getMessage() );
            }
        } elseif ($is_fiat_listed && !$is_crypto_listed) {
            throw new Exception("Crypto currency is not listed!");
        } elseif (!$is_fiat_listed && $is_crypto_listed) {
            throw new Exception("Fiat currency is not listed!");
        } else {
            throw new Exception("Crypto and fiat currencies are not listed!");
        }
    }

    public function calculateAmountOfCoins(float $credit, float $exchange_rate): float
    {
        return $credit / $exchange_rate;
    }

    public function getEnteredValues(string $user_input, int $max_value): array | bool
    {
        $allowed_characters = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ',', ' '];
        $entered_characters = str_split($user_input);
        foreach ($entered_characters as $character) {
            if ( !in_array($character, $allowed_characters) ) {
                echo "Error";
                return false;
            }
        }
        $favourites = explode(',', $user_input);
        foreach ($favourites as &$item) {
            $item = str_replace(' ', '', $item);    // trim()?
        }
        unset($item);

        foreach($favourites as $item) {
            if ( (int) $item > $max_value ) {
                return false;   // error handling!!
            }
        }
        return($favourites);
    }

    public function getFavourites(array $data, array $index_array) : array | bool
    {
        $favourite_currencies = array();
        foreach ($index_array as $index) {
            array_push($favourite_currencies, $data[(int) $index - 1]);
        }
        return $favourite_currencies;
    }

    private function getApiData(string $api_endpoint): array
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
}
