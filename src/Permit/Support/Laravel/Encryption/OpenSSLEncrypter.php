<?php namespace Permit\Support\Laravel\Encryption;

use Illuminate\Contracts\Encryption\Encrypter;

class OpenSSLEncrypter implements Encrypter
{

    /**
     * The second half of the method parameter of openssl_encrypt
     * (e.g cbc for aes-128-cbc)
     *
     * @var string
     **/
    protected $mode = 'cbc';

    /**
     * The first half of the method parameter of openssl_encrypt
     *
     * @var string
     **/
    protected $cipher = 'aes-128';

    /**
     * The password parameter of openssl_encrypt
     *
     * @var string
     **/
    protected $key;

    /**
     * The iv, if not set it will be generated
     *
     * @var string
     **/
    protected $iv;

    /**
     * The key as a hex number
     *
     * @var string
     **/
    protected $encodedKey;


    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Encrypt the given value.
     *
     * @param  string  $value
     * @param  bool    $serialize (default:true)
     * @return string
     */
    public function encrypt($value, $serialize = true)
    {
        $iv = $this->getIv();
        $method = $this->method();
        $password = $this->encodedKey();
        $value = serialize($value);

        $encrypted = openssl_encrypt($value, $method, $password, true, $iv);

        return base64_encode($encrypted);

    }

    /**
     * Decrypt the given value.
     *
     * @param  string  $payload
     * @param  bool    $unserialize (default:true)
     * @return string
     */
    public function decrypt($payload, $unserialize = true)
    {
        $iv = $this->getIv();
        $method = $this->method();
        $password = $this->encodedKey();
        return unserialize(openssl_decrypt($payload, $method, $password, false, $iv));
    }

    /**
     * Set the encryption mode.
     *
     * @param  string  $mode
     * @return void
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Set the encryption cipher.
     *
     * @param  string  $cipher
     * @return void
     */
    public function setCipher($cipher)
    {
        $this->cipher = $cipher;
    }

    public function method()
    {
        return implode('-', [$this->cipher, $this->mode]);
    }

    public function encodedKey()
    {
        if (!$this->encodedKey) {
            $this->encodedKey = $this->toEncryptionKey($this->key, $this->keyLength());
        }
        return $this->encodedKey;
    }

    public function toEncryptionKey($string, $length, $encoding=null)
    {
        $encoding = $encoding ?: mb_internal_encoding();
        $hexString = $this->stringToHex($string, $encoding);
        $hexStringLength = strlen($hexString);

        if ($hexStringLength == $length) {
            return $hexString;
        }

        if ($hexStringLength > $length) {
            return substr($hexString, 0, $length);
        }

        $factor = (int)ceil(min($length/$hexStringLength, $length));

        return substr(str_repeat($hexString, $factor), 0, $length);

    }

    public function stringToHex($string)
    {
        $hex='';
        foreach ($this->splitIntoChars($string) as $char) {
            $hex .= strtoupper(dechex(ord($char)));
        }
        return $hex;
    }

    public function splitIntoChars($string, $encoding=null)
    {

        $encoding = $encoding ?: mb_internal_encoding();
        $strlen = mb_strlen($string, $encoding);
        $splittedString = [];

        while ($strlen) {
            $splittedString[] = mb_substr($string, 0, 1, $encoding);
            $string = mb_substr($string, 1, $strlen, $encoding);
            $strlen = mb_strlen($string, $encoding);
        }

        return $splittedString;

    }

    public function keyLength()
    {
        return openssl_cipher_iv_length($this->method());
    }

    public function getIv()
    {
        if ($this->iv === null) {
            $this->iv = strrev($this->encodedKey());
        }
        return $this->iv;

    }

    public function setIv($iv)
    {
        $this->iv = $iv;
    }

}
