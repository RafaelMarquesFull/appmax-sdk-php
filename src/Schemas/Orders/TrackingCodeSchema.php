<?php

namespace Appmax\Schemas\Orders;

use Appmax\Structures\AppmaxAPIError;

class TrackingCodeSchema
{
    /**
     * Validate and transform tracking code data
     * 
     * @param array $input Tracking code data
     * @return array Validated and transformed data
     * @throws AppmaxAPIError If validation fails
     */
    public static function validate(array $input): array
    {
        // Validate required fields
        if (empty($input['orderHash'])) {
            throw new AppmaxAPIError('INVALID_ORDER_HASH', 'Order hash is required');
        }
        
        if (empty($input['trackingCode'])) {
            throw new AppmaxAPIError('INVALID_TRACKING_CODE', 'Tracking code is required');
        }
        
        // Transform input to API format
        return [
            'order_hash' => $input['orderHash'],
            'tracking_code' => $input['trackingCode'],
        ];
    }
}
