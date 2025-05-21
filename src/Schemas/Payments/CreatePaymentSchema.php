<?php

namespace Appmax\Schemas\Payments;

use Appmax\Structures\AppmaxAPIError;

class CreatePaymentSchema
{
    /**
     * Validate and transform payment data
     * 
     * @param array $input Payment data
     * @return array Validated and transformed data
     * @throws AppmaxAPIError If validation fails
     */
    public static function validate(array $input): array
    {
        // Validate required fields
        self::validateRequired($input, ['orderHash', 'method']);
        
        // Validate payment method
        $validMethods = ['credit_card', 'billet', 'pix'];
        if (!in_array($input['method'], $validMethods)) {
            throw new AppmaxAPIError(
                'INVALID_PAYMENT_METHOD',
                "Payment method must be one of: " . implode(', ', $validMethods)
            );
        }
        
        // Validate method-specific fields
        if ($input['method'] === 'credit_card') {
            self::validateCreditCardPayment($input);
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
            if (!isset($input[$field]) || $input[$field] === '') {
                throw new AppmaxAPIError('MISSING_FIELD', "Field '{$field}' is required");
            }
        }
    }
    
    /**
     * Validate credit card payment data
     * 
     * @param array $input Payment data
     * @throws AppmaxAPIError If validation fails
     */
    private static function validateCreditCardPayment(array $input): void
    {
        if (!isset($input['installments']) || !is_numeric($input['installments']) || $input['installments'] < 1) {
            throw new AppmaxAPIError(
                'INVALID_INSTALLMENTS',
                "Installments must be a positive number for credit card payments"
            );
        }
        
        if (!isset($input['card']) || !is_array($input['card'])) {
            throw new AppmaxAPIError('MISSING_CARD_DATA', "Card data is required for credit card payments");
        }
        
        $requiredFields = ['token', 'holder', 'brand'];
        
        if (isset($input['card']['number'])) {
            // If using direct card number instead of token
            $requiredFields = ['number', 'holder', 'expiry', 'cvv', 'brand'];
        }
        
        foreach ($requiredFields as $field) {
            if (!isset($input['card'][$field]) || $input['card'][$field] === '') {
                throw new AppmaxAPIError('MISSING_CARD_FIELD', "Card field '{$field}' is required");
            }
        }
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
            'order_hash' => $input['orderHash'],
            'method' => $input['method'],
        ];
        
        if ($input['method'] === 'credit_card') {
            $result['installments'] = $input['installments'];
            
            if (isset($input['card']['token'])) {
                $result['card'] = [
                    'token' => $input['card']['token'],
                    'holder' => $input['card']['holder'],
                    'brand' => $input['card']['brand'],
                ];
            } else {
                $result['card'] = [
                    'number' => $input['card']['number'],
                    'holder' => $input['card']['holder'],
                    'expiry' => $input['card']['expiry'],
                    'cvv' => $input['card']['cvv'],
                    'brand' => $input['card']['brand'],
                ];
            }
        }
        
        return $result;
    }
}
