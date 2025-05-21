<?php

namespace Appmax;

use Appmax\Assertions\Literal;
use Appmax\Managers\APIManager;
use Appmax\Managers\CustomersManager;
use Appmax\Managers\OrdersManager;
use Appmax\Managers\PaymentsManager;

class AppmaxAPI
{
    public static array $apiInfo = [
        'version' => 'v3',
        'baseUrl' => 'https://admin.appmax.com.br/api',
        'testBaseUrl' => 'https://homolog.sandboxappmax.com.br/api',
    ];

    public readonly APIManager $api;
    public readonly CustomersManager $customers;
    public readonly OrdersManager $orders;
    public readonly PaymentsManager $payments;

    public function __construct(string $apiKey, array $options = [])
    {
        Literal::assertString($apiKey, 'API_KEY');
        $testMode = $options['testMode'] ?? false;
        $this->api = new APIManager($apiKey, $testMode);
        $this->customers = new CustomersManager($this);
        $this->orders = new OrdersManager($this);
        $this->payments = new PaymentsManager($this);
    }
}
