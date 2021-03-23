<?php

/**
 * This file is part of the tron-php package
 *
 * @category tron-php
 * @package  tron-php
 * @author   Fenguoz <243944672@qq.com>
 * @license  https://github.com/Fenguoz/tron-php/blob/master/LICENSE MIT
 * @link     https://github.com/Fenguoz/tron-php
 */

namespace Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Tron\Address;
use Tron\Api;
use Tron\TRC20;

class TRC20Test extends TestCase
{
    const URI = 'https://api.shasta.trongrid.io'; // shasta testnet
    const ADDRESS = 'TGytofNKuSReFmFxsgnNx19em3BAVBTpVB';
    const PRIVATE_KEY = '0xf1b4b7d86a3eff98f1bace9cb2665d0cad3a3f949bc74a7ffb2aaa968c07f521';
    const BLOCK_ID = 13402554;
    const TX_HASH = '539e6c2429f19a8626fadc1211985728e310f5bd5d2749c88db2e3f22a8fdf69';
    const CONTRACT = [
        'contract_address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t', // USDT TRC20
        'decimals' => 6,
    ];

    private function getTRC20()
    {
        $api = new Api(new Client(['base_uri' => self::URI]));
        $config = self::CONTRACT;
        $trxWallet = new TRC20($api, $config);
        return $trxWallet;
    }

    public function testGenerateAddress()
    {
        $addressData = $this->getTRC20()->generateAddress();
        var_dump($addressData);

        $this->assertTrue(true);
    }

    public function testPrivateKeyToAddress()
    {
        $privateKey = self::PRIVATE_KEY;
        $addressData = $this->getTRC20()->privateKeyToAddress($privateKey);
        var_dump($addressData);

        $this->assertTrue(true);
    }

    public function testBalance()
    {
        $address = new Address(
            self::ADDRESS,
            '',
            $this->getTRC20()->tron->address2HexString(self::ADDRESS)
        );
        $balanceData = $this->getTRC20()->balance($address);
        var_dump($balanceData);

        $this->assertTrue(true);
    }

    public function testTransfer()
    {
        $privateKey = self::PRIVATE_KEY;
        $address = self::ADDRESS;
        $amount = 1;

        $from = $this->getTRC20()->privateKeyToAddress($privateKey);
        $to = new Address(
            $address,
            '',
            $this->getTRC20()->tron->address2HexString($address)
        );
        $transferData = $this->getTRC20()->transfer($from, $to, $amount);
        var_dump($transferData);

        $this->assertTrue(true);
    }

    public function testBlockNumber()
    {
        $blockData = $this->getTRC20()->blockNumber();
        var_dump($blockData);

        $this->assertTrue(true);
    }

    public function testBlockByNumber()
    {
        $blockID = self::BLOCK_ID;
        $blockData = $this->getTRC20()->blockByNumber($blockID);
        var_dump($blockData);

        $this->assertTrue(true);
    }

    public function testTransactionReceipt()
    {
        $txHash = self::TX_HASH;
        $txData = $this->getTRC20()->transactionReceipt($txHash);
        var_dump($txData);

        $this->assertTrue(true);
    }
}
