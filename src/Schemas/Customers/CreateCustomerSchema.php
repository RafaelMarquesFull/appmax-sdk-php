<?php

namespace Appmax\Schemas\Customers;

use Appmax\Structures\AppmaxAPIError;

class CreateCustomerSchema
{
    /**
     * Validate and transform customer data
     * 
     * @param array $input Customer data
     * @return array Validated and transformed data
     * @throws AppmaxAPIError If validation fails
     */
    public static function validate(array $input): array
    {
        // Validate required fields
        self::validateRequired($input, ['firstName', 'lastName', 'email', 'telephone', 'ip']);
        
        // Validate email format
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            throw new AppmaxAPIError('INVALID_EMAIL', 'Email format is invalid');
        }
        
        // Validate string lengths
        self::validateStringLength($input['firstName'], 'firstName', 1, 100);
        self::validateStringLength($input['lastName'], 'lastName', 1, 100);
        self::validateStringLength($input['telephone'], 'telephone', 1, 11);
        
        // Validate address if provided
        if (isset($input['address'])) {
            self::validateAddress($input['address']);
        }
        
        // Validate optional fields
        if (isset($input['customText'])) {
            self::validateStringLength($input['customText'], 'customText', 1, 255);
        }
        
        // Validate products if provided
        if (isset($input['products'])) {
            self::validateProducts($input['products']);
        }
        
        // Validate UTM tracking if provided
        if (isset($input['utmTracking'])) {
            self::validateUtmTracking($input['utmTracking']);
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
     * Validate string length
     * 
     * @param string $value String to validate
     * @param string $fieldName Field name for error message
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @throws AppmaxAPIError If string length is invalid
     */
    private static function validateStringLength($value, string $fieldName, int $min, int $max): void
    {
        $length = mb_strlen((string)$value);
        if ($length < $min || $length > $max) {
            throw new AppmaxAPIError(
                'INVALID_LENGTH',
                "Field '{$fieldName}' must be between {$min} and {$max} characters"
            );
        }
    }
    
    /**
     * Validate address data
     * 
     * @param array $address Address data
     * @throws AppmaxAPIError If address validation fails
     */
    private static function validateAddress(array $address): void
    {
        self::validateRequired($address, ['postcode', 'street', 'number', 'district', 'city', 'state']);
        
        // Validate postcode length
        if (mb_strlen($address['postcode']) !== 8) {
            throw new AppmaxAPIError('INVALID_POSTCODE', 'Postcode must be exactly 8 characters');
        }
        
        // Validate state length
        if (mb_strlen($address['state']) !== 2) {
            throw new AppmaxAPIError('INVALID_STATE', 'State must be exactly 2 characters');
        }
        
        // Validate string lengths
        self::validateStringLength($address['street'], 'address.street', 1, 255);
        self::validateStringLength($address['number'], 'address.number', 1, 56);
        self::validateStringLength($address['district'], 'address.district', 1, 255);
        self::validateStringLength($address['city'], 'address.city', 1, 255);
        
        if (isset($address['complement'])) {
            self::validateStringLength($address['complement'], 'address.complement', 1, 255);
        }
    }
    
    /**
     * Validate products data
     * 
     * @param array $products Products data
     * @throws AppmaxAPIError If products validation fails
     */
    private static function validateProducts(array $products): void
    {
        foreach ($products as $index => $product) {
            if (!isset($product['sku']) || !isset($product['quantity'])) {
                throw new AppmaxAPIError(
                    'INVALID_PRODUCT',
                    "Product at index {$index} must have 'sku' and 'quantity' fields"
                );
            }
            
            self::validateStringLength($product['sku'], "products[{$index}].sku", 1, 100);
            
            if (!is_numeric($product['quantity']) || $product['quantity'] <= 0) {
                throw new AppmaxAPIError(
                    'INVALID_QUANTITY',
                    "Product quantity at index {$index} must be a positive number"
                );
            }
        }
    }
    
    /**
     * Validate UTM tracking data
     * 
     * @param array $utmTracking UTM tracking data
     * @throws AppmaxAPIError If UTM tracking validation fails
     */
    private static function validateUtmTracking(array $utmTracking): void
    {
        $utmFields = ['source', 'campaign', 'medium', 'content', 'term'];
        
        foreach ($utmFields as $field) {
            if (isset($utmTracking[$field])) {
                self::validateStringLength($utmTracking[$field], "utmTracking.{$field}", 1, 255);
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
            'firstname' => $input['firstName'],
            'lastname' => $input['lastName'],
            'email' => $input['email'],
            'telephone' => $input['telephone'],
            'ip' => $input['ip'],
        ];
        
        // Add address fields if provided
        if (isset($input['address'])) {
            $result['postcode'] = $input['address']['postcode'];
            $result['address_street'] = $input['address']['street'];
            $result['address_street_number'] = $input['address']['number'];
            $result['address_street_district'] = $input['address']['district'];
            $result['address_city'] = $input['address']['city'];
            $result['address_state'] = $input['address']['state'];
            
            if (isset($input['address']['complement'])) {
                $result['address_street_complement'] = $input['address']['complement'];
            }
        }
        
        // Add custom text if provided
        if (isset($input['customText'])) {
            $result['custom_txt'] = $input['customText'];
        }
        
        // Add products if provided
        if (isset($input['products'])) {
            $result['products'] = array_map(function ($product) {
                return [
                    'product_sku' => $product['sku'],
                    'product_qty' => $product['quantity'],
                ];
            }, $input['products']);
        }
        
        // Add UTM tracking if provided
        if (isset($input['utmTracking'])) {
            $result['tracking'] = [
                'utm_source' => $input['utmTracking']['source'] ?? null,
                'utm_campaign' => $input['utmTracking']['campaign'] ?? null,
                'utm_medium' => $input['utmTracking']['medium'] ?? null,
                'utm_content' => $input['utmTracking']['content'] ?? null,
                'utm_term' => $input['utmTracking']['term'] ?? null,
            ];
        }
        
        return $result;
    }
}
