<?php

namespace Tron;

use IEXBase\TronAPI\Support\Base58Check;
use IEXBase\TronAPI\Support\Hash;

class Address
{
    public $privateKey,
        $address,
        $hexAddress = '';

    const ADDRESS_SIZE = 34;
    const ADDRESS_PREFIX = "41";
    const ADDRESS_PREFIX_BYTE = 0x41;

    public function __construct(string $address = '', string $privateKey = '', string $hexAddress = '')
    {
        if (strlen($address) === 0) {
            throw new \InvalidArgumentException('Address can not be empty');
        }

        $this->privateKey = $privateKey;
        $this->address = $address;
        $this->hexAddress = $hexAddress;
    }

    /**
     * Dont rely on this. Always use Wallet::validateAddress to double check
     * against tronGrid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (strlen($this->address) !== Address::ADDRESS_SIZE) {
            return false;
        }

        $address = Base58Check::decode($this->address, false, 0, false);
        $utf8 = hex2bin($address);

        if (strlen($utf8) !== 25) {
            return false;
        }

        if (strpos($utf8, chr(self::ADDRESS_PREFIX_BYTE)) !== 0) {
            return false;
        }

        $checkSum = substr($utf8, 21);
        $address = substr($utf8, 0, 21);

        $hash0 = Hash::SHA256($address);
        $hash1 = Hash::SHA256($hash0);
        $checkSum1 = substr($hash1, 0, 4);

        if ($checkSum === $checkSum1) {
            return true;
        }

        return false;
    }
}
