<?php

namespace Tron;

use kornrunner\Keccak;
use Phactor\Key;
use IEXBase\TronAPI\Exception\TronException;
use IEXBase\TronAPI\Tron;
use IEXBase\TronAPI\Provider\HttpProvider;
use Tron\Interfaces\WalletInterface;
use Tron\Exceptions\TronErrorException;
use Tron\Exceptions\TransactionException;
use IEXBase\TronAPI\Support\Base58;
use IEXBase\TronAPI\Support\Crypto;
use IEXBase\TronAPI\Support\Hash;

class TRX implements WalletInterface
{
    public function __construct(Api $_api, $config)
    {
        $this->_api = $_api;

        $fullNode = new HttpProvider($config['rpc_url']);
        $solidityNode = new HttpProvider($config['rpc_url']);
        $eventServer = new HttpProvider($config['rpc_url']);
        try {
            $this->tron = new Tron($fullNode, $solidityNode, $eventServer);
        } catch (TronException $e) {
            throw new TronErrorException($e->getMessage(), $e->getCode());
        }
    }

    public function genKeyPair(): array
    {
        $key = new Key();

        return $key->GenerateKeypair();
    }

    public function getAddressHex(string $pubKeyBin): string
    {
        if (strlen($pubKeyBin) == 65) {
            $pubKeyBin = substr($pubKeyBin, 1);
        }

        $hash = Keccak::hash($pubKeyBin, 256);

        return Address::ADDRESS_PREFIX . substr($hash, 24);
    }

    public function getBase58CheckAddress(string $addressBin): string
    {
        $hash0 = Hash::SHA256($addressBin);
        $hash1 = Hash::SHA256($hash0);
        $checksum = substr($hash1, 0, 4);
        $checksum = $addressBin . $checksum;

        return Base58::encode(Crypto::bin2bc($checksum));
    }

    public function generateAddress(): Address
    {
        $attempts = 0;
        $validAddress = false;

        do {
            if ($attempts++ === 5) {
                throw new TronErrorException('Could not generate valid key');
            }

            $keyPair = $this->genKeyPair();
            $privateKeyHex = $keyPair['private_key_hex'];
            $pubKeyHex = $keyPair['public_key'];

            //We cant use hex2bin unless the string length is even.
            if (strlen($pubKeyHex) % 2 !== 0) {
                continue;
            }

            $pubKeyBin = hex2bin($pubKeyHex);
            $addressHex = $this->getAddressHex($pubKeyBin);
            $addressBin = hex2bin($addressHex);
            $addressBase58 = $this->getBase58CheckAddress($addressBin);

            $address = new Address($addressBase58, $privateKeyHex, $addressHex);
            $validAddress = $this->validateAddress($address);
        } while (!$validAddress);

        return $address;
    }

    public function validateAddress(Address $address): bool
    {
        if (!$address->isValid()) {
            return false;
        }

        $body = $this->_api->post('/wallet/validateaddress', [
            'address' => $address->address,
        ]);

        return $body->result;
    }

    public function balance(Address $address)
    {
        $this->tron->setAddress($address->address);
        return $this->tron->getBalance(null, true);
    }

    public function transfer(Address $from, Address $to, float $amount): Transaction
    {
        $this->tron->setAddress($from->address);
        $this->tron->setPrivateKey($from->privateKey);

        try {
            $transaction = $this->tron->getTransactionBuilder()->sendTrx($to->address, $amount, $from->address);
            $signedTransaction = $this->tron->signTransaction($transaction);
            $response = $this->tron->sendRawTransaction($signedTransaction);
        } catch (TronException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode());
        }

        if (isset($response['result']) && $response['result'] == true) {
            return new Transaction(
                $transaction['txID'],
                $transaction['raw_data'],
                'PACKING'
            );
        } else {
            throw new TransactionException('transfer fail');
        }
    }

    public function blockNumber(): Block
    {
        try {
            $block = $this->tron->getCurrentBlock();
        } catch (TronException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode());
        }
        return new Block($block['blockID'], $block['block_header']);
    }

    public function blockByNumber(int $blockID): Block
    {
        try {
            $block = $this->tron->getBlockByNumber($blockID);
        } catch (TronException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode());
        }
        return new Block($block['blockID'], $block['block_header'], $block['transactions']);
    }

    public function transactionReceipt(string $txHash): Transaction
    {
        try {
            $detail = $this->tron->getTransaction($txHash);
        } catch (TronException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode());
        }
        return new Transaction(
            $detail['txID'],
            $detail['raw_data'],
            $detail['ret'][0]['contractRet']
        );
    }
}
