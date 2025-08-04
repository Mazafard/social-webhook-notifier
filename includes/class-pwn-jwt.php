<?php
/**
 * JWT Helper class
 */

if (!defined('ABSPATH')) exit;

class PWN_JWT {
    
    /**
     * JWT instance
     */
    private static $instance = null;
    
    /**
     * Get JWT instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Initialize JWT functionality
    }
    
    /**
     * Generate JWT token
     *
     * @param string $algorithm JWT algorithm
     * @param string $key_type Key type (secret or pem)
     * @param string $key Secret or PEM key
     * @param string $passphrase PEM key passphrase (optional)
     * @param string $payload_template JSON payload template
     * @return string|false JWT token or false on failure
     */
    public function generate_token($algorithm, $key_type, $key, $passphrase = '', $payload_template = '') {
        try {
            // Parse payload template
            $payload = $this->parse_payload_template($payload_template);
            if (!$payload) {
                PWN_Logger::error('Invalid JWT payload template');
                return false;
            }
            
            // Create JWT header
            $header = array(
                'typ' => 'JWT',
                'alg' => $algorithm
            );
            
            // Encode header and payload
            $header_encoded = $this->base64url_encode(json_encode($header));
            $payload_encoded = $this->base64url_encode(json_encode($payload));
            
            // Create signature
            $signature = $this->create_signature($algorithm, $header_encoded . '.' . $payload_encoded, $key_type, $key, $passphrase);
            if ($signature === false) {
                return false;
            }
            
            // Combine parts
            return $header_encoded . '.' . $payload_encoded . '.' . $signature;
            
        } catch (Exception $e) {
            PWN_Logger::error('JWT generation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Parse payload template and replace placeholders
     *
     * @param string $template Payload template
     * @return array|false Parsed payload array or false on failure
     */
    private function parse_payload_template($template) {
        if (empty($template)) {
            // Default payload
            $template = '{"iss": "wordpress", "iat": {{timestamp}}}';
        }
        
        // Replace timestamp placeholders
        $current_time = time();
        $template = str_replace('{{timestamp}}', $current_time, $template);
        $template = preg_replace_callback('/{{timestamp\+(\d+)}}/', function($matches) use ($current_time) {
            return $current_time + intval($matches[1]);
        }, $template);
        $template = preg_replace_callback('/{{timestamp-(\d+)}}/', function($matches) use ($current_time) {
            return $current_time - intval($matches[1]);
        }, $template);
        
        // Parse JSON
        $payload = json_decode($template, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            PWN_Logger::error('JWT payload JSON parse error: ' . json_last_error_msg());
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Create JWT signature
     *
     * @param string $algorithm Algorithm
     * @param string $data Data to sign
     * @param string $key_type Key type
     * @param string $key Key data
     * @param string $passphrase Passphrase
     * @return string|false Signature or false on failure
     */
    private function create_signature($algorithm, $data, $key_type, $key, $passphrase = '') {
        switch ($algorithm) {
            case 'HS256':
                return $this->base64url_encode(hash_hmac('sha256', $data, $key, true));
            case 'HS384':
                return $this->base64url_encode(hash_hmac('sha384', $data, $key, true));
            case 'HS512':
                return $this->base64url_encode(hash_hmac('sha512', $data, $key, true));
            case 'RS256':
            case 'RS384':
            case 'RS512':
            case 'PS256':
            case 'PS384':
            case 'PS512':
            case 'ES256':
            case 'ES384':
            case 'ES512':
                return $this->create_rsa_signature($algorithm, $data, $key, $passphrase);
            default:
                PWN_Logger::error('Unsupported JWT algorithm: ' . $algorithm);
                return false;
        }
    }
    
    /**
     * Create RSA/ECDSA signature
     *
     * @param string $algorithm Algorithm
     * @param string $data Data to sign
     * @param string $key PEM key
     * @param string $passphrase Passphrase
     * @return string|false Signature or false on failure
     */
    private function create_rsa_signature($algorithm, $data, $key, $passphrase = '') {
        // Load private key
        if (!empty($passphrase)) {
            $private_key = openssl_pkey_get_private($key, $passphrase);
        } else {
            $private_key = openssl_pkey_get_private($key);
        }
        
        if (!$private_key) {
            PWN_Logger::error('Failed to load private key for JWT signing');
            return false;
        }
        
        // Map algorithm to OpenSSL constants
        $algo_map = array(
            'RS256' => OPENSSL_ALGO_SHA256,
            'RS384' => OPENSSL_ALGO_SHA384,
            'RS512' => OPENSSL_ALGO_SHA512,
            'PS256' => OPENSSL_ALGO_SHA256,
            'PS384' => OPENSSL_ALGO_SHA384,
            'PS512' => OPENSSL_ALGO_SHA512,
            'ES256' => OPENSSL_ALGO_SHA256,
            'ES384' => OPENSSL_ALGO_SHA384,
            'ES512' => OPENSSL_ALGO_SHA512
        );
        
        if (!isset($algo_map[$algorithm])) {
            PWN_Logger::error('Unsupported RSA/ECDSA algorithm: ' . $algorithm);
            return false;
        }
        
        $signature = '';
        $success = false;
        
        // Handle PSS algorithms
        if (strpos($algorithm, 'PS') === 0) {
            // PSS requires specific flags (PHP 7.2+)
            if (defined('OPENSSL_ALGO_SHA256') && version_compare(PHP_VERSION, '7.2.0', '>=')) {
                $success = openssl_sign($data, $signature, $private_key, $algo_map[$algorithm] | OPENSSL_KEYTYPE_RSA);
            } else {
                PWN_Logger::error('PSS algorithms require PHP 7.2+ with OpenSSL support');
                return false;
            }
        } else {
            // Standard RSA/ECDSA
            $success = openssl_sign($data, $signature, $private_key, $algo_map[$algorithm]);
        }
        
        if (!$success) {
            PWN_Logger::error('Failed to create JWT signature');
            return false;
        }
        
        return $this->base64url_encode($signature);
    }
    
    /**
     * Base64 URL encode
     *
     * @param string $data Data to encode
     * @return string Encoded data
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}