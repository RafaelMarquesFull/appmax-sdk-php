<?php

namespace Appmax\Schemas\Orders;

use Appmax\Structures\AppmaxAPIError;

class RefundSchema
{
    /**
     * Validate and transform refund data
     * 
     * @param array $input Refund data
     * @return array Validated and transformed data
     * @throws AppmaxAPIError If validation fails
     */
    public static function validate(array $input): array
    {
        // Validate required fields
        if (empty($input['orderHash'])) {
            throw new AppmaxAPIError('INVALID_ORDER_HASH', 'Order hash is required');
        }
        
        // Transform input to API format
        $result = [
            'order_hash' => $input['orderHash'],
        ];
        
        // Add options if provided
        if (isset($input['options']) && is_array($input['options'])) {
            if (isset($input['options']['reason'])) {
                $result['reason'] = $input['options']['reason'];
            }
            
            if (isset($input['options']['amount']) && is_numeric($input['options']['amount']) && $input['options']['amount'] > 0) {
                $result['amount'] = $input['options']['amount'];
            }
        }
        
        return $result;
    }
}
