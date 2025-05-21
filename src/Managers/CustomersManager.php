<?php

namespace Appmax\Managers;

use Appmax\AppmaxAPI;
use Appmax\Schemas\Customers\CreateCustomerSchema;

class CustomersManager
{
    private AppmaxAPI $client;

    public function __construct(AppmaxAPI $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new customer
     * 
     * @param array $customer Customer data
     * @return array Created customer data
     * @throws \Appmax\Structures\AppmaxAPIError If validation fails or API request fails
     */
    public function create(array $customer): array
    {
        $payload = CreateCustomerSchema::validate($customer);
        $response = $this->client->api->fetch('customer', [
            'method' => 'POST',
            'body' => $payload,
        ]);

        return $this->assertCreateCustomerResponse($response['data']);
    }

    /**
     * Assert and format create customer response
     * 
     * @param array $data Response data
     * @return array Formatted customer data
     */
    private function assertCreateCustomerResponse(array $data): array
    {
        // Transform response data to match the expected format
        return [
            'id' => $data['id'] ?? null,
            'hash' => $data['hash'] ?? null,
            'firstName' => $data['firstname'] ?? null,
            'lastName' => $data['lastname'] ?? null,
            'email' => $data['email'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'address' => isset($data['postcode']) ? [
                'postcode' => $data['postcode'],
                'street' => $data['address_street'],
                'number' => $data['address_street_number'],
                'complement' => $data['address_street_complement'] ?? null,
                'district' => $data['address_street_district'],
                'city' => $data['address_city'],
                'state' => $data['address_state'],
                'uf' => $data['uf'] ?? null,
            ] : null,
            'documentNumber' => $data['document_number'] ?? null,
            'siteId' => $data['site_id'] ?? null,
            'ip' => $data['ip'] ?? null,
            'customText' => $data['custom_txt'] ?? null,
            'createdAt' => $data['created_at'] ?? null,
            'updatedAt' => $data['updated_at'] ?? null,
        ];
    }
}
