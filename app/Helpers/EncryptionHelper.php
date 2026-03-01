<?php
/**
 * EncryptionHelper
 * Handles AES-256-CBC encryption for sensitive student data
 */

namespace App\Helpers;

class EncryptionHelper {
    private static $method = 'aes-256-cbc';

    /**
     * Encrypt a string
     */
    public static function encrypt($text) {
        if (empty($text)) return null;
        
        $key = hash('sha256', PII_ENCRYPTION_KEY);
        $ivSize = openssl_cipher_iv_length(self::$method);
        $iv = openssl_random_pseudo_bytes($ivSize);
        
        $encrypted = openssl_encrypt($text, self::$method, $key, 0, $iv);
        
        // Combine IV and Encrypted text (base64 encoded for storage)
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a string
     */
    public static function decrypt($cipher) {
        if (empty($cipher)) return null;
        
        try {
            $data = base64_decode($cipher);
            $key = hash('sha256', PII_ENCRYPTION_KEY);
            $ivSize = openssl_cipher_iv_length(self::$method);
            
            $iv = substr($data, 0, $ivSize);
            $encrypted = substr($data, $ivSize);
            
            return openssl_decrypt($encrypted, self::$method, $key, 0, $iv);
        } catch (\Exception $e) {
            return $cipher; // Fallback to raw if decryption fails (e.g. data wasn't encrypted)
        }
    }
}
