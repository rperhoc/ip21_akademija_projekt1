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
        return sprintf(self::API_BASE . 'prices/%s-%s/%s', $crypto, $fiat, $price);
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

    public function getEnteredValues(string $user_input): array | bool
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

        return($favourites);
    }

    public function saveFavourites(array $data, array $index_array) : array | bool
    {
        $favourite_currencies = array();
        foreach ($index_array as $index) {
            if ($index > count($this->cryptoCurrencies)) {
                echo "Index out of range!\n";
                exit();
            }
            array_push($favourite_currencies, $data[(int) $index - 1]);
        }
        return $favourite_currencies;
    }

    public function pdoConnect(string $server, string $dbname, string $user = "root", string $pass = "root")
    {
        $dsn = "mysql:host=$server;dbname=$dbname";
        try {
            $db = new PDO($dsn, $user, $pass);
        } catch (Exception $e) {
            throw new Exception( "Error connecting to database: " . $e->getMessage() );
        }

        try {
            $connection = new mysqli($server, $user, $pass, $dbname);
        } catch (Exception $e) {
            throw new Exception ( "Error connecting to database: " . $e->getMessage() );
        }
        return $connection;
    }

    public function markFavourites() : bool
    {
        echo "Do you wish to favourite any currency? (y / n)\n";
        $answer = trim(fgets(STDIN));

        while ( !($answer == 'y' || $answer == 'n') ) {
            echo "Please enter 'y' or 'n':\n";
            $answer = trim(fgets(STDIN));
        }
        if ($answer == 'n') {
            return false;
        }
        return true;
    }

    public function isCurrencyFavourite(object $db, string $currency) : bool
    {
        $stmt = $db->prepare("SELECT 1 FROM favourites WHERE code = ?");
        $stmt->execute([$currency]);
        $result = $stmt->fetch();

        return $result > 0;
    }

    public function getFavouritesArray(object $db, array $currency_array, string $currency_type) : array
    {
        $favourites_array = array ();
        switch ($currency_type) {
            case 'crypto':
                foreach ($currency_array as $key => $value) {
                    if ( $this->isCurrencyFavourite($db, $value['code']) ) {
                        array_push($favourites_array, $value);
                    }
                }
                break;
            case 'fiat':
                foreach ($currency_array as $key => $value) {
                    if ( $this->isCurrencyFavourite($db, $value['id']) ) {
                        array_push($favourites_array, $value);
                    }
                }
                break;
            }
        return $favourites_array;
    }

    public function pushFavouritesOnTop(array $currency_array, array $favourites_array) : array
    {
        $new_array = $favourites_array;
        $difference = array_diff_assoc($currency_array, $favourites_array);
        foreach ($difference as $key => $value) {
            array_push($new_array, $value);
        }
        return $new_array;
    }

    public function getCurrencyFromName(string $name, string $type) : array
    {
        switch ($type) {
            case 'crypto':
                $crypto_data = $this->getCryptoData();
                foreach ($crypto_data as $key => $value) {
                    if ($name == $value['code']) {
                        return $value;
                    }
                }
                $error_message = "Crypto currency is not listed: " . $name . "\n";
                throw new Exception($error_message);
                    break;
            case 'fiat':
                $fiat_data = $this->getFiatData();
                foreach ($fiat_data as $key => $value) {
                    if ($name == $value['id']) {
                        return $value;
                    }
                }
                throw new Exception("Fiat currency is not listed!\n");
                break;
        }
    }

    public function insertIntoFavourites(object $db, array $currency, string $type) : void
    {
        $stmt = $db->prepare("INSERT INTO favourites (code, name, type) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE code = VALUES(code), name = VALUES(name), type = VALUES(type)");
        if ($type == 'fiat') {
            $stmt->execute([$currency['id'], $currency['name'], $type]);
        }else {
            $stmt->execute([$currency['code'], $currency['name'], $type]);
        }
    }

    public function removeFromFavourites(object $db, string $currency) : void
    {
        $stmt = $db->prepare("DELETE FROM favourites WHERE code = ?");
        $stmt->execute([$currency]);
    }

    public function selectFromFavourites(object $db) : void
    {
        $stmt = $db->prepare("SELECT * FROM favourites");
        $stmt->execute();
        $stmt->bind_result($id, $code, $name);

        while ($stmt->fetch()) {
            echo "ID: " . $id . "\n";
            echo "Code: " . $code . "\n";
            echo "Name: " . $name . "\n";
            echo "\n\n";
        }
    }

    private function getApiData(string $api_endpoint): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_close($ch);
        $data = json_decode(curl_exec($ch), true);

        if (isset( $data['errors']) || $data === null ) {
            $error_message = "Error(s) retrieving data from API endpoint:\n";
            if ( isset($data['errors']) ){
                foreach ($data['errors'] as $key => $value) {
                    $error_message .= "$value\n";
                }
            }
            throw new Exception($error_message);
        }
        return $data['data'];
    }
}
