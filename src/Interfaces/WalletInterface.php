<?php

namespace Tron\Interfaces;

use Tron\Address;
use Tron\Block;
use Tron\Transaction;

interface WalletInterface
{
    public function generateAddress(): Address;

    public function validateAddress(Address $address): bool;

    public function privateKeyToAddress(string $privateKeyHex): Address;

    public function balance(Address $address);

    public function transfer(Address $from, Address $to, float $amount): Transaction;

    public function blockNumber(): Block;

    public function blockByNumber(int $blockID): Block;

    public function transactionReceipt(string $txHash);
}
