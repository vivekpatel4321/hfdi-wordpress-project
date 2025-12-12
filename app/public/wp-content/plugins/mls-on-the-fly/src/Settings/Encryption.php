<?php

namespace Realtyna\MlsOnTheFly\Settings;

/**
 * Class Settings
 *
 * @package Realtyna\MlsOnTheFly\Settings
 */
class Encryption
{
    /**
     * Encrypts (encodes) a given string using a special key.
     * 
     * @author Chris A <chris.a@realtyna.net>
     *
     * @param string $plaintext  The text to be encrypted.
     * @param string $specialKey The secret key used for encryption.
     *
     * @return string
     */
    public static function encode($plaintext, $specialKey)
    {
        $cipherMethod = 'AES-256-CBC';        
        $ivLength = openssl_cipher_iv_length($cipherMethod);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encryptedText = openssl_encrypt($plaintext, $cipherMethod, $specialKey, 0, $iv);
        
        if ($encryptedText === false) {
            throw new \Exception("Encryption failed.");
        }
        
        $encryptedWithIV = $iv . $encryptedText;
        
        return base64_encode($encryptedWithIV);
    }
    
    /**
     * Decrypts (decodes) an encoded string using the special key.
     *
     * @author Chris A <chris.a@realtyna.net>
     * 
     * @param string $encodedString The base64-encoded encrypted string.
     * @param string $specialKey    The secret key used for decryption (must be the same as used for encryption).
     *
     * @return string 
     */
    public static function decode($encodedString, $specialKey)
    {
        $cipherMethod = 'AES-256-CBC';
        $encryptedData = base64_decode($encodedString);
        $ivLength = openssl_cipher_iv_length($cipherMethod);
        $iv = substr($encryptedData, 0, $ivLength);
        $encryptedText = substr($encryptedData, $ivLength);
        $decryptedText = openssl_decrypt($encryptedText, $cipherMethod, $specialKey, 0, $iv);
        
        if ($decryptedText === false) {
            throw new \Exception("Decryption failed.");
        }
        
        return $decryptedText;
    }   
}
