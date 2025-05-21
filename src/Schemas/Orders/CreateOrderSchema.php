<?php

namespace Appmax\Schemas\Orders;

use Appmax\Structures\AppmaxAPIError;

class CreateOrderSchema
{
    /**
     * Validate and transform order data
     * 
     * @param array $input Order data
     * @return array Validated and transformed data
     * @throws AppmaxAPIError If validation fails
     */
    public static function validate(array $input): array
    {
        // Validate required fields
        self::validateRequired($input, ['customerHash', 'total', 'items']);
        
        // Validate customer hash
        if (empty($input['customerHash'])) {
            throw new AppmaxAPIError('INVALID_CUSTOMER_HASH', 'Customer hash is required');
        }
        
        // Validate total
        if (!is_numeric($input['total']) || $input['total'] <= 0) {
            throw new AppmaxAPIError('INVALID_TOTAL', 'Total must be a positive number');
        }
        
        // Validate items
        if (!is_array($input['items']) || empty($input['items'])) {
            throw new AppmaxAPIError('INVALID_ITEMS', 'Items must be a non-empty array');
        }
        
        self::validateItems($input['items']);
        
        // Validate payment if provided
        if (isset($input['payment'])) {
            self::validatePayment($input['payment']);
        }
        
        // Transform input to API format
        return self::transformInput($input);
    }
    
    /**
     * Validate required fields
     * 
     * @param array $input Input data
     * @param array $requiredFields List of required field names
     * @throws AppmaxAPIError If any required field is missing
     */
    private static function validateRequired(array $input, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                throw new AppmaxAPIError('MISSING_FIELD', "Field '{$field}' is required");
            }
        }
    }
    
    /**
     * Validate order items
     * 
     * @param array $items Order items
     * @throws AppmaxAPIError If items validation fails
     */
    private static function validateItems(array $items): void
    {
        foreach ($items as $index => $item) {
            if (!isset($item['name']) || !isset($item['price']) || !isset($item['quantity'])) {
                throw new AppmaxAPIError(
                    'INVALID_ITEM',
                    "Item at index {$index} must have 'name', 'price', and 'quantity' fields"
                );
            }
            
            if (empty($item['name'])) {
                throw new AppmaxAPIError('INVALID_ITEM_NAME', "Item name at index {$index} cannot be empty");
            }
            
            if (!is_numeric($item['price']) || $item['price'] <= 0) {
                throw new AppmaxAPIError(
                    'INVALID_ITEM_PRICE',
                    "Item price at index {$index} must be a positive number"
                );
            }
            
            if (!is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                throw new AppmaxAPIError(
                    'INVALID_ITEM_QUANTITY',
                    "Item quantity at index {$index} must be a positive number"
                );
            }
        }
    }
    
    /**
     * Validate payment data
     * 
     * @param array $payment Payment data
     * @throws AppmaxAPIError If payment validation fails
     */
    private static function validatePayment(array $payment): void
    {
        self::validateRequired($payment, ['method']);
        
        $validMethods = ['credit_card', 'billet', 'pix'];
        if (!in_array($payment['method'], $validMethods)) {
            throw new AppmaxAPIError(
                'INVALID_PAYMENT_METHOD',
                "Payment method must be one of: " . implode(', ', $validMethods)
            );
        }
        
        if ($payment['method'] === 'credit_card') {
            if (!isset($payment['installments']) || !is_numeric($payment['installments']) || $payment['installments'] < 1) {
                throw new AppmaxAPIError(
                    'INVALID_INSTALLMENTS',
                    "Installments must be a positive number for credit card payments"
                );
            }
            
            if (!isset($payment['card']) || !is_array($payment['card'])) {
                throw new AppmaxAPIError('MISSING_CARD_DATA', "Card data is required for credit card payments");
            }
            
            self::validateCardData($payment['card']);
        }
    }
    
    /**
     * Validate card data
     * 
     * @param array $card Card data
     * @throws AppmaxAPIError If card data validation fails
     */
    private static function validateCardData(array $card): void
    {
        $requiredFields = ['token', 'holder', 'brand'];
        
        if (isset($card['number'])) {
            // If using direct card number instead of token
            $requiredFields = ['number', 'holder', 'expiry', 'cvv', 'brand'];
        }
        
        self::validateRequired($card, $requiredFields);
    }
    
    /**
     * Transform input to API format
     * 
     * @param array $input Validated input data
     * @return array Transformed data for API
     */
    private static function transformInput(array $input): array
    {
        $result = [
            'customer_hash' => $input['customerHash'],
            'total' => $input['total'],
            'items' => array_map(function ($item) {
                return [
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'qty' => $item['quantity'],
                    'sku' => $item['sku'] ?? null,
                ];
            }, $input['items']),
        ];
        
        // Add payment if provided
        if (isset($input['payment'])) {
            $result['payment'] = [
                'method' => $input['payment']['method'],
            ];
            
            if ($input['payment']['method'] === 'credit_card') {
                $result['payment']['installments'] = $input['payment']['installments'];
                
                if (isset($input['payment']['card']['token'])) {
                    $result['payment']['card'] = [
                        'token' => $input['payment']['card']['token'],
                        'holder' => $input['payment']['card']['holder'],
                        'brand' => $input['payment']['card']['brand'],
                    ];
                } else {
                    $result['payment']['card'] = [
                        'number' => $input['payment']['card']['number'],
                        'holder' => $input['payment']['card']['holder'],
                        'expiry' => $input['payment']['card']['expiry'],
                        'cvv' => $input['payment']['card']['cvv'],
                        'brand' => $input['payment']['card']['brand'],
                    ];
                }
            }
        }
        
        return $result;
    }
}
