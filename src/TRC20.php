<?php

namespace Tron;

use IEXBase\TronAPI\Exception\TronException;
use Tron\Exceptions\TransactionException;
use Tron\Support\Formatter;
use Tron\Support\Utils;

class TRC20 extends TRX
{
    protected $contractAddress;

    public function __construct(Api $_api, array $config)
    {
        parent::__construct($_api, $config);

        $this->contractAddress = new Address(
            $config['contract_address'],
            '',
            $this->tron->address2HexString($config['contract_address'])
        );
        $this->decimals = $config['decimals'];
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
            throw new TronException(hex2bin($body->result->message));
        }
        return Utils::toDisplayAmount($body->constant_result[0], $this->decimals);
    }

    public function transfer(Address $from, Address $to, float $amount): Transaction
    {
        $this->tron->setAddress($from->address);
        $this->tron->setPrivateKey($from->privateKey);

        $toFormat = Formatter::toAddressFormat($to->hexAddress);
        $amount = Utils::toMinUnitByDecimals($amount, $this->decimals);
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
            $tradeobj = $this->tron->setIsOnlineSign(false)->signTransaction($body['transaction']);
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
            throw new TransactionException('Transfer Fail');
        }
    }
}
