<h1 align="center">TRON-PHP</h1>

## 概述

TRON-PHP 目前支持波场的 TRX 和 TRC20 中常用生成地址，发起转账，离线签名等功能。

## 特点

1. 一套写法兼容 TRON 网络中 TRX 货币和 TRC 系列所有通证
1. 接口方法可可灵活增减

## 支持方法

- 生成地址 `generateAddress()`
- 验证地址 `validateAddress(Address $address)`
- 根据私钥得到地址 `privateKeyToAddress(string $privateKeyHex)`
- 查询余额 `balance(Address $address)`
- 交易转账(离线签名) `transfer(Address $from, Address $to, float $amount)`
- 查询最新区块 `blockNumber()`
- 根据区块链查询信息 `blockByNumber(int $blockID)`
- 根据交易哈希查询信息 `transactionReceipt(string $txHash)`

## 快速开始

### 安装

``` php
composer require fenguoz/tron-php
```

### 接口调用

``` php
use GuzzleHttp\Client;

$uri = 'https://api.shasta.trongrid.io';// shasta testnet
$api = new \Tron\Api(new Client(['base_uri' => $uri]));

$trxWallet = new \Tron\TRX($api);
$addressData = $trxWallet->generateAddress();
// $addressData->privateKey
// $addressData->address

$config = [
    'contract_address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',// USDT TRC20
    'decimals' => 6,
];
$trc20Wallet = new \Tron\TRC20($api, $this->config);
$addressData = $trc20Wallet->generateAddress();
```

## 计划

- 支持 TRC10
- 测试用例
- ...

## 扩展包

| 扩展包名 | 描述 | 应用场景 |
| :-----| :---- | :---- |
| [iexbase/tron-api](https://github.com/iexbase/tron-api) | 波场官方文档推荐 PHP 扩展包 | 波场基础Api |