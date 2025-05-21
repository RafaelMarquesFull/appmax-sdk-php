<?php

namespace Appmax\Schemas\Payments;

use Appmax\Structures\AppmaxAPIError;

class TokenizeSchema
{
    /**
     * Validate and transform tokenize card data
     * 
     * @param array $input Card data
     * @return array Validated and transformed data
     * @throws AppmaxAPIError If validation fails
     */
    public static function validate(array $input): array
    {
        // Validate required fields
        $requiredFields = ['number', 'holder', 'expiry', 'cvv', 'brand'];
        
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                throw new AppmaxAPIError('MISSING_CARD_FIELD', "Card field '{$field}' is required");
            }
        }
        
        // Validate card number (basic validation)
        if (!preg_match('/^\d{13,19}$/', preg_replace('/\s+/', '', $input['number']))) {
            throw new AppmaxAPIError('INVALID_CARD_NUMBER', 'Card number is invalid');
        }
        
        // Validate expiry format (MM/YY or MM/YYYY)
        if (!preg_match('/^\d{2}\/\d{2}(\d{2})?$/', $input['expiry'])) {
            throw new AppmaxAPIError('INVALID_EXPIRY', 'Expiry date format must be MM/YY or MM/YYYY');
        }
        
        // Validate CVV (3-4 digits)
        if (!preg_match('/^\d{3,4}$/', $input['cvv'])) {
            throw new AppmaxAPIError('INVALID_CVV', 'CVV must be 3 or 4 digits');
        }
        
        // Transform input to API format
        return [
            'number' => preg_replace('/\s+/', '', $input['number']),
            'holder' => $input['holder'],
            'expiry' => $input['expiry'],
            'cvv' => $input['cvv'],
            'brand' => $input['brand'],
        ];
    }
}
