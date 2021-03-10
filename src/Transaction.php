<?php

namespace Tron;

class Transaction
{
    public $signature = [];
    public $txID = '';
    public $raw_data = [];
    public $contractRet = '';

    public function __construct(string $txID, array $rawData, string $contractRet)
    {
        $this->txID = $txID;
        $this->raw_data = $rawData;
        $this->contractRet = $contractRet;
    }

    public function isSigned(): bool
    {
        return (bool)sizeof($this->signature);
    }
}
