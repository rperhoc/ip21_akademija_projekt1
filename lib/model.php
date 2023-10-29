<?php

class Model
{
    private const API_BASE = 'https://api.coinbase.com/v2/';
    private $hostname;
    private $dbport;
    private $dbname;
    private $user;
    private $pass;
    private $db = null;
    private $fiatCurrencies = null;
    private $cryptoCurrencies = null;
    private $allowedParameters = ['invalid_email', 'invalid_password', 'error', 'email_taken'];

    public function __construct() 
    {
        $this->hostname = $_ENV['DB_HOST'];
        $this->dbport = $_ENV['DB_PORT'];
        $this->dbname = $_ENV['DB_DATABASE'];
        $this->user = 'root';
        $this->pass = 'root';
        
        $this->cryptoCurrencies = $this->getCryptoData();
        $this->fiatCurrencies = $this->getFiatData();
        
        foreach ($this->cryptoCurrencies as $key => $value) {
            array_push($this->allowedParameters, $value['code']);
        }
        foreach ($this->fiatCurrencies as $key => $value) {
            array_push($this->allowedParameters, $value['id']);
        }
        
        $this->db = $this->pdoConnect();
    }    

    public function validateInputArgs(array $args): bool
    {
        for ($i = 1; $i < sizeof($args); $i++) {
            if ( (strlen($args[$i]) > 20) ) {
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
            } catch (Exception $e) {
                throw new Exception ( $e->getMessage() );
            }
        }
        return $this->cryptoCurrencies;
    }

    public function listCryptoCurrencies()
    {
        foreach ($this->cryptoCurrencies as $key => $value) {
            echo ($key + 1) . "\t{$value['code']}\t{$value['name']}\n";
        }
    }

    public function listFiatCurrencies()
    {
        foreach ($this->fiatCurrencies as $key => $value) {
            echo ($key + 1) . "\t{$value['id']}\t{$value['name']}\n";
        }
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
            $item = str_replace(' ', '', $item); 
        }
        unset($item);

        return($favourites);
    }

    public function saveFavourites(array $data, array $indexes) : array | bool
    {
        $favourites = array();
        foreach ($indexes as $index) {
            if ( $index > count($this->cryptoCurrencies) ) {
                echo "Index out of range!\n";
                exit();
            }
            array_push($favourites, $data[(int) $index - 1]);
        }
        return $favourites;
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

    public function isCurrencyFavourite(string $currency, int $user_id = 0) : bool 
    {
        $db = $this->pdoConnect();
        $stmt = $db->prepare("SELECT 1 FROM favourites WHERE user_id = ? AND code = ?");
        $stmt->execute([$user_id, $currency]);
        $result = $stmt->fetch();

        return $result > 0;
    }

    public function getFavourites(string $type, int $user_id = 0) : array
    {
        $db = $this->pdoConnect();
        $favourites = array ();
        switch ($type) {
            case 'crypto':
                $currencies = $this->getCryptoData();
                foreach ($currencies as $key => $value) {
                    if ( $this->isCurrencyFavourite($value['code'], $user_id) ) {
                        array_push($favourites, $value);
                    }
                }
                break;
            case 'fiat':
                $currencies = $this->getFiatData();
                foreach ($currencies as $key => $value) {
                    if ( $this->isCurrencyFavourite($value['id'], $user_id) ) {
                        array_push($favourites, $value);
                    } 
                }
                break;
            default:
                throw new Exception("Invalid currency type entered. Please enter 'crypto' or 'fiat'\n");
            }
        return $favourites;
    }

    public function getSortedCurrencies(string $type, int $user_id = 0) : array
    {
        switch ($type) {
            case 'crypto':
                try {
                    $currencies = $this->getCryptoData();
                    $favourites = $this->getFavourites('crypto', $user_id);
                } catch (Exception $e) {
                    throw new Exception( "Error connecting to database: " . $e->getMessage() );
                }
                foreach ($currencies as $key => $value) {
                    if ( in_array($value, $favourites)) {
                        unset($currencies[$key]);
                    }
                }
                foreach ($currencies as $key => $value) {
                    array_push($favourites, $currencies[$key]);
                }
                break;
            case 'fiat':
                try {
                    $currencies = $this->getFiatData();
                    $favourites = $this->getFavourites('fiat', $user_id);
                } catch (Exception $e) {
                    throw new Exception( "Error connecting to database: " . $e->getMessage() );
                }
                foreach ($currencies as $key => $value) {
                    if (in_array($value, $favourites)) {
                        unset($currencies[$key]);
                    }
                }
                foreach ($currencies as $key => $value) {
                    array_push($favourites, $currencies[$key]);
                }
                break;
            default:
                throw new Exception("Invalid currency type given. Enter 'crypto' or 'fiat'\n");
        }
        return $favourites;      
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

    public function insertIntoFavourites(array $currency, string $type, int $user_id = 0) : void
    {
        $db = $this->pdoConnect();
        $stmt = $db->prepare("INSERT INTO favourites (user_id, code, name, type) VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE code = VALUES(code), name = VALUES(name), type = VALUES(type)");
        if ($type == 'fiat') {
            $stmt->execute([$user_id, $currency['id'], $currency['name'], $type]);            
        }else {
            $stmt->execute([$user_id, $currency['code'], $currency['name'], $type]);
        }
    }

    public function removeFromFavourites(string $currency, int $user_id = 0) : void 
    {
        $db = $this->pdoConnect();
        $stmt = $db->prepare("DELETE FROM favourites WHERE user_id = ? AND code = ?");
        $stmt->execute([$user_id, $currency]);
    }

    public function selectFromFavourites(int $user_id = 0) : void
    {
        $db = $this->pdoConnect();
        $stmt = $db->prepare("SELECT * FROM favourites WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            echo "code: " . $row['code'] . "\n";
            echo "name: " . $row['name'] . "\n";            
            echo "ID: " . $row['id'] . "\n";
            echo "type: " . $row['type'] . "\n";
            echo "\n\n";
        }
    } 

    public function listParameters() : void
    {
        foreach($this->allowedParameters as $key => $value) {
            echo $value . "\n";
        }
    }

    public function verifyParameter(string $parameter) : bool 
    {
        return in_array($parameter, $this->allowedParameters);
    }

    public function verifyParameters(array $parameters) : bool 
    {
        foreach ($parameters as $key => $value) {
            if (!$this->verifyParameter($key) || !$this->verifyParameter($value)) {
                return false;
            }
        }
        return true;
    }

    public function assignParameter(string $parameter) : string
    {
        if ( $this->verifyParameter($parameter) ) {
            return $parameter;
        } 
        throw new Exception("Parameter ${parameter} is not whitelisted!\n");
    }

    public function assignParameters(array $parameters) : array
    {
        $assigned_parameters = array();
        foreach ($parameters as $key => $value) {
            try {
                $assigned_parameters[$key] = $this->assignParameter($value);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        return $assigned_parameters;
    }

    public function getRedirectUrl(array $parameters) : string
    {
        $url = '/show_price.php?';
        foreach ($parameters as $key => $value) {
            if ( !$this->verifyParameter($value) ) {
                throw new Exception("Error getting URL: Parameter ${parameter} is not whitelisted!\n");
            } else {
                if ($key === 'crypto' || $key === 'fiat') {
                    $url .= "${key}=${value}&";
                }
            }
        }
        return substr($url, 0, -1);
    }

    public function addUser(string $email, string $password) 
    {
        $db = $this->pdoConnect();
        $stmt = $db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->execute([$email, password_hash($password, PASSWORD_BCRYPT)]);
    }

    public function isPasswordValid(string $password) : bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password);
    }

    public function isEmailValid(string $email) : bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function isUserRegistered(?string  $email) : bool
    {
        if ($email == null) {
            return false;
        }
        $db = $this->pdoConnect();
        $stmt = $db->prepare("SELECT 1 FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();

        return $result > 0;
    }

    public function verifyLogin(string $email, string $password) : bool 
    {
        return password_verify($password, $this->getUserPassword($email));
    }

    public function getUserId(string $email) : int
    {
        $db = $this->pdoConnect();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function getCurrencyType(array $currency) : string
    {
        if (isset($currency['code'])) {
            return 'crypto';
        } elseif (isset($currency['id'])) {
            return 'fiat';
        }
        throw new Exception("Invalid entry.\n");
    }

    public function getSessionStatus() : string
    {
        $status = session_status();
        if ($status == PHP_SESSION_ACTIVE) {
            return "Session is active";
        } elseif ($status == PHP_SESSION_NONE) {
            return "Session is enabled but not started";
        } elseif ($status == PHP_SESSION_DISABLED) {
            return "Sessions are disabled";
        }
        return "something else";
    }

    public function setUserId() : int
    {
        if (!isset($_SESSION['user_id'])) {
            return 0;
        } else {
            return $_SESSION['user_id'];
        }
    }
    
    private function getUserPassword(string $email) 
    {
        $db = $this->pdoConnect();
        $stmt = $db->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }
    
    private function pdoConnect() : PDO
    {
        if ($this->db === null) {
            $dsn = "mysql:host={$this->hostname};port={$this->dbport};dbname={$this->dbname}";
            try {
                $this->db = new PDO($dsn, $this->user, $this->pass);
            } catch (Exception $e) {
                throw new Exception( "Error connecting to database: " . $e->getMessage() );
            }
        }
        return $this->db;     
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
