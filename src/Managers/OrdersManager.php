<?php

namespace Appmax\Managers;

use Appmax\AppmaxAPI;
use Appmax\Schemas\Orders\CreateOrderSchema;
use Appmax\Schemas\Orders\RefundSchema;
use Appmax\Schemas\Orders\TrackingCodeSchema;

class OrdersManager
{
    private AppmaxAPI $client;

    public function __construct(AppmaxAPI $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new order
     * 
     * @param array $order Order data
     * @return array Created order data
     * @throws \Appmax\Structures\AppmaxAPIError If validation fails or API request fails
     */
    public function create(array $order): array
    {
        $payload = CreateOrderSchema::validate($order);
        $response = $this->client->api->fetch('order', [
            'method' => 'POST',
            'body' => $payload,
        ]);

        return $this->assertCreateOrderResponse($response['data']);
    }

    /**
     * Add tracking code to an order
     * 
     * @param string $orderHash Order hash
     * @param string $trackingCode Tracking code
     * @return array Response data
     * @throws \Appmax\Structures\AppmaxAPIError If validation fails or API request fails
     */
    public function addTrackingCode(string $orderHash, string $trackingCode): array
    {
        $payload = TrackingCodeSchema::validate([
            'orderHash' => $orderHash,
            'trackingCode' => $trackingCode
        ]);
        
        $response = $this->client->api->fetch('order/tracking', [
            'method' => 'POST',
            'body' => $payload,
        ]);

        return $response['data'];
    }

    /**
     * Refund an order
     * 
     * @param string $orderHash Order hash
     * @param array $options Refund options
     * @return array Response data
     * @throws \Appmax\Structures\AppmaxAPIError If validation fails or API request fails
     */
    public function refund(string $orderHash, array $options = []): array
    {
        $payload = RefundSchema::validate([
            'orderHash' => $orderHash,
            'options' => $options
        ]);
        
        $response = $this->client->api->fetch('order/refund', [
            'method' => 'POST',
            'body' => $payload,
        ]);

        return $response['data'];
    }

    /**
     * Assert and format create order response
     * 
     * @param array $data Response data
     * @return array Formatted order data
     */
    private function assertCreateOrderResponse(array $data): array
    {
        // Transform response data to match the expected format
        return [
            'id' => $data['id'] ?? null,
            'hash' => $data['hash'] ?? null,
            'status' => $data['status'] ?? null,
            'customerHash' => $data['customer_hash'] ?? null,
            'paymentHash' => $data['payment_hash'] ?? null,
            'total' => $data['total'] ?? null,
            'createdAt' => $data['created_at'] ?? null,
            'updatedAt' => $data['updated_at'] ?? null,
        ];
    }
}
