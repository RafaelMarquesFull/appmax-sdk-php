<?php

namespace Appmax\Schemas\Payments;

use Appmax\Structures\AppmaxAPIError;

class InstallmentsSchema
{
    /**
     * Validate and transform installments request data
     * 
     * @param array $input Installments request data
     * @return array Validated and transformed data
     * @throws AppmaxAPIError If validation fails
     */
    public static function validate(array $input): array
    {
        // Validate required fields
        if (!isset($input['amount']) || !is_numeric($input['amount']) || $input['amount'] <= 0) {
            throw new AppmaxAPIError('INVALID_AMOUNT', 'Amount must be a positive number');
        }
        
        if (empty($input['brand'])) {
            throw new AppmaxAPIError('INVALID_BRAND', 'Card brand is required');
        }
        
        // Transform input to API format
        return [
            'amount' => $input['amount'],
            'brand' => $input['brand'],
        ];
    }
}
