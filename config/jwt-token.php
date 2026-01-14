<?php
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    die("Veuillez installer les dépendances Composer. Exécutez 'composer install' dans le dossier racine de votre projet.");
}

require_once $autoloadPath;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JWTUtils
{
    private static $secretKey = "MRV@78!12UT";
    private static $algorithm = 'HS256';
    private static $tokenExpiration = 432000; // 5 jours en secondes
    
    public static function generateJWT($payload)
    {
        $now = time();
        $payload['iat'] = $now;
        $payload['exp'] = $now + self::$tokenExpiration;
        
        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    public static function validateJWT($token)
    {
        try {
            return (array) JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
        } catch (ExpiredException $e) {
            throw new Exception("Session expirée");
        } catch (SignatureInvalidException $e) {
            throw new Exception("Session invalide");
        } catch (Exception $e) {
            throw new Exception("Session invalide");
        }
    }

    public static function getBearerToken()
    {
        $headers = self::getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        throw new Exception('Token manquant');
    }

    public static function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
}
