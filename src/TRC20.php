<?php

namespace Tron;

use IEXBase\TronAPI\Exception\TronException;
use Tron\Exceptions\TransactionException;
use Tron\Exceptions\TronErrorException;
use Tron\Support\Formatter;
use Tron\Support\Utils;
use InvalidArgumentException;

class TRC20 extends TRX
{
    protected $contractAddress;
    protected $decimals;
    protected $trc20Contract;

    public function __construct(Api $_api, array $config)
    {
        parent::__construct($_api, $config);

        if (!isset($config['contract_address']) || empty($config['contract_address'])) {
            throw new TronErrorException('Missing contract address');
        }
        if (!isset($config['decimals']) || empty($config['decimals'])) {
            throw new TronErrorException('Missing decimals');
        }

        $this->contractAddress = new Address($config['contract_address']);
        $this->decimals = $config['decimals'];
        $this->trc20Contract = new \IEXBase\TronAPI\TRC20Contract(
            $this->tron,
            $config['contract_address'],
            (isset($config['abi']) ? $config['abi'] : null)
        );
    }

    public function balance(Address $address)
    {
        $format = Formatter::toAddressFormat($address->hexAddress);
        $body = $this->_api->post('/wallet/triggersmartcontract', [
            'contract_address' => $this->contractAddress->hexAddress,
            'function_selector' => 'balanceOf(address)',
            'parameter' => $format,
            'owner_address' => $address->hexAddress,
        ]);

        if (isset($body->result->code)) {
            throw new TronErrorException(hex2bin($body->result->message));
        }

        try {
            $balance = Utils::toDisplayAmount(hexdec($body->constant_result[0]), $this->decimals);
        } catch (InvalidArgumentException $e) {
            throw new TronErrorException($e->getMessage());
        }
        return $balance;
    }

    public function transfer(Address $from, Address $to, float $amount): Transaction
    {
        $this->tron->setAddress($from->address);
        $this->tron->setPrivateKey($from->privateKey);

        $toFormat = Formatter::toAddressFormat($to->hexAddress);
        try {
            $amount = Utils::toMinUnitByDecimals($amount, $this->decimals);
        } catch (InvalidArgumentException $e) {
            throw new TronErrorException($e->getMessage());
        }
        $numberFormat = Formatter::toIntegerFormat($amount);

        $body = $this->_api->post('/wallet/triggersmartcontract', [
            'contract_address' => $this->contractAddress->hexAddress,
            'function_selector' => 'transfer(address,uint256)',
            'parameter' => "{$toFormat}{$numberFormat}",
            'fee_limit' => 100000000,
            'call_value' => 0,
            'owner_address' => $from->hexAddress,
        ], true);

        if (isset($body['result']['code'])) {
            throw new TransactionException(hex2bin($body['result']['message']));
        }

        try {
            $tradeobj = $this->tron->signTransaction($body['transaction']);
            $response = $this->tron->sendRawTransaction($tradeobj);
        } catch (TronException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode());
        }

        if (isset($response['result']) && $response['result'] == true) {
            return new Transaction(
                $body['transaction']['txID'],
                $body['transaction']['raw_data'],
                'PACKING'
            );
        } else {
            throw new TransactionException(hex2bin($response['result']['message']));
        }
    }

    public function walletTransactions(Address $address, int $limit = null): ?array
    {
        try {
            $ret = $this->trc20Contract->getTransactions($address->address, $limit);
        } catch (TronException $e) {
            throw new TronErrorException($e->getMessage(), $e->getCode());
        }

        return $ret['data'];
    }
}
