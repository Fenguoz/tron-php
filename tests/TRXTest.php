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
use Tron\TRX;

class TRXTest extends TestCase
{
    const URI = 'https://api.shasta.trongrid.io'; // shasta testnet
    const ADDRESS = 'TGytofNKuSReFmFxsgnNx19em3BAVBTpVB';
    const PRIVATE_KEY = '0xf1b4b7d86a3eff98f1bace9cb2665d0cad3a3f949bc74a7ffb2aaa968c07f521';
    const BLOCK_ID = 13402554;
    const TX_HASH = '539e6c2429f19a8626fadc1211985728e310f5bd5d2749c88db2e3f22a8fdf69';

    private function getTRX()
    {
        $api = new Api(new Client(['base_uri' => self::URI]));
        $trxWallet = new TRX($api);
        return $trxWallet;
    }

    public function testGenerateAddress()
    {
        $addressData = $this->getTRX()->generateAddress();
        var_dump($addressData);

        $this->assertTrue(true);
    }

    public function testPrivateKeyToAddress()
    {
        $privateKey = self::PRIVATE_KEY;
        $addressData = $this->getTRX()->privateKeyToAddress($privateKey);
        var_dump($addressData);

        $this->assertTrue(true);
    }

    public function testBalance()
    {
        $address = new Address(
            self::ADDRESS,
            '',
            $this->getTRX()->tron->address2HexString(self::ADDRESS)
        );
        $balanceData = $this->getTRX()->balance($address);
        var_dump($balanceData);

        $this->assertTrue(true);
    }

    public function testTransfer()
    {
        $privateKey = self::PRIVATE_KEY;
        $address = self::ADDRESS;
        $amount = 1;

        $from = $this->getTRX()->privateKeyToAddress($privateKey);
        $to = new Address(
            $address,
            '',
            $this->getTRX()->tron->address2HexString($address)
        );
        $transferData = $this->getTRX()->transfer($from, $to, $amount);
        var_dump($transferData);

        $this->assertTrue(true);
    }

    public function testBlockNumber()
    {
        $blockData = $this->getTRX()->blockNumber();
        var_dump($blockData);

        $this->assertTrue(true);
    }

    public function testBlockByNumber()
    {
        $blockID = self::BLOCK_ID;
        $blockData = $this->getTRX()->blockByNumber($blockID);
        var_dump($blockData);

        $this->assertTrue(true);
    }

    public function testTransactionReceipt()
    {
        $txHash = self::TX_HASH;
        $txData = $this->getTRX()->transactionReceipt($txHash);
        var_dump($txData);

        $this->assertTrue(true);
    }
}
