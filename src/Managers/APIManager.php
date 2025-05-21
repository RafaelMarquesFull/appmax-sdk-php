<?php

namespace Appmax\Managers;

use Appmax\AppmaxAPI;
use Appmax\Structures\AppmaxAPIError;

class APIManager
{
    public readonly string $baseUrl;

    public function __construct(
        private readonly string $apiKey,
        private readonly bool $testMode = false
    ) {
        $apiInfo = AppmaxAPI::$apiInfo;
        $baseUrl = $this->testMode ? $apiInfo['testBaseUrl'] : $apiInfo['baseUrl'];
        $this->baseUrl = $baseUrl . '/' . $apiInfo['version'] . '/';
    }

    /**
     * Fetch data from the API
     * 
     * @param string $path API endpoint path
     * @param array $requestInit Request options
     * @return array API response payload
     * @throws AppmaxAPIError If the request fails
     */
    public function fetch(string $path, array $requestInit = []): array
    {
        $url = $this->baseUrl . $path;
        $init = $this->parseInit($requestInit);
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $init['method'],
            CURLOPT_POSTFIELDS => $init['body'] ?? null,
            CURLOPT_HTTPHEADER => $init['headers'] ?? [],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        if ($err) {
            throw new AppmaxAPIError('UNKNOWN_ERROR', $err);
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['success']) || $data['success'] !== true || $httpCode < 200 || $httpCode >= 300) {
            $errorMessage = $data['text'] ?? 'Unknown API error';
            $errorData = $data['data'] ?? null;
            throw new AppmaxAPIError('API_ERROR', $errorMessage, $errorData);
        }
        
        return $data;
    }

    /**
     * Parse request initialization options
     * 
     * @param array $init Request options
     * @return array Parsed request options
     */
    private function parseInit(array $init): array
    {
        $method = strtoupper($init['method'] ?? 'GET');
        $init['method'] = $method;
        
        $headers = $init['headers'] ?? [];
        
        if ($method === 'POST' && isset($init['body'])) {
            $init['body']['access-token'] = $this->apiKey;
            $headers[] = 'Content-Type: application/json';
            $init['body'] = json_encode($init['body']);
        }
        
        $init['headers'] = $headers;
        
        return $init;
    }
}
