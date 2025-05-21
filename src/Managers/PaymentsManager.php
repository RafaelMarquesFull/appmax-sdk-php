<?php

namespace Appmax\Managers;

use Appmax\AppmaxAPI;
use Appmax\Schemas\Payments\CreatePaymentSchema;
use Appmax\Schemas\Payments\InstallmentsSchema;
use Appmax\Schemas\Payments\TokenizeSchema;

class PaymentsManager
{
    private AppmaxAPI $client;

    public function __construct(AppmaxAPI $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new payment
     * 
     * @param array $payment Payment data
     * @return array Created payment data
     * @throws \Appmax\Structures\AppmaxAPIError If validation fails or API request fails
     */
    public function create(array $payment): array
    {
        $payload = CreatePaymentSchema::validate($payment);
        $response = $this->client->api->fetch('payment', [
            'method' => 'POST',
            'body' => $payload,
        ]);

        return $this->assertCreatePaymentResponse($response['data']);
    }

    /**
     * Get available installments
     * 
     * @param float $amount Payment amount
     * @param string $brand Card brand
     * @return array Available installments
     * @throws \Appmax\Structures\AppmaxAPIError If validation fails or API request fails
     */
    public function getInstallments(float $amount, string $brand): array
    {
        $payload = InstallmentsSchema::validate([
            'amount' => $amount,
            'brand' => $brand
        ]);
        
        $response = $this->client->api->fetch('payment/installments', [
            'method' => 'POST',
            'body' => $payload,
        ]);

        return $response['data'];
    }

    /**
     * Tokenize card data
     * 
     * @param array $cardData Card data
     * @return array Tokenized card data
     * @throws \Appmax\Structures\AppmaxAPIError If validation fails or API request fails
     */
    public function tokenize(array $cardData): array
    {
        $payload = TokenizeSchema::validate($cardData);
        $response = $this->client->api->fetch('payment/tokenize', [
            'method' => 'POST',
            'body' => $payload,
        ]);

        return $response['data'];
    }

    /**
     * Assert and format create payment response
     * 
     * @param array $data Response data
     * @return array Formatted payment data
     */
    private function assertCreatePaymentResponse(array $data): array
    {
        // Transform response data to match the expected format
        return [
            'id' => $data['id'] ?? null,
            'hash' => $data['hash'] ?? null,
            'status' => $data['status'] ?? null,
            'orderHash' => $data['order_hash'] ?? null,
            'amount' => $data['amount'] ?? null,
            'installments' => $data['installments'] ?? null,
            'paymentMethod' => $data['payment_method'] ?? null,
            'createdAt' => $data['created_at'] ?? null,
            'updatedAt' => $data['updated_at'] ?? null,
        ];
    }
}
