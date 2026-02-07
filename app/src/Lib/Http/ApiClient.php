<?php

namespace App\Lib\Http;

use App\Lib\Cache\TransientManager;

class ApiClient
{
    private int $timeout = 10;
    private array $defaultHeaders = [];
    private int $cacheExpiration = 3600;

    public function __construct()
    {
        TransientManager::init();
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function addHeader(string $key, string $value): self
    {
        $this->defaultHeaders[$key] = $value;
        return $this;
    }

    public function setCacheExpiration(int $seconds): self
    {
        $this->cacheExpiration = $seconds;
        return $this;
    }

    public function getCached(string $url, array $params = [], ?int $cacheExpiration = null): ?array
    {
        $cacheKey = $this->generateCacheKey('GET', $url, $params);

        $cached = TransientManager::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $result = $this->get($url, $params);

        if ($result !== null) {
            $expiration = $cacheExpiration ?? $this->cacheExpiration;
            TransientManager::set($cacheKey, $result, $expiration);
        }

        return $result;
    }

    public function get(string $url, array $params = []): ?array
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->request('GET', $url);
    }

    public function post(string $url, array $data = []): ?array
    {
        return $this->request('POST', $url, $data);
    }

    public function put(string $url, array $data = []): ?array
    {
        return $this->request('PUT', $url, $data);
    }

    public function delete(string $url): ?array
    {
        return $this->request('DELETE', $url);
    }

    public function invalidateCache(string $url, array $params = []): void
    {
        $cacheKey = $this->generateCacheKey('GET', $url, $params);
        TransientManager::delete($cacheKey);
    }

    private function request(string $method, string $url, array $data = []): ?array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $headers = $this->prepareHeaders();
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($data) && in_array($method, ['POST', 'PUT'])) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            error_log("ApiClient Error: $error");
            return null;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("ApiClient HTTP Error: $httpCode - $response");
            return null;
        }

        return $this->parseResponse($response);
    }

    private function prepareHeaders(): array
    {
        $headers = [];
        
        foreach ($this->defaultHeaders as $key => $value) {
            $headers[] = "$key: $value";
        }

        if (empty($this->defaultHeaders['Content-Type'])) {
            $headers[] = 'Content-Type: application/json';
        }

        return $headers;
    }

    private function parseResponse(string $response): ?array
    {
        $data = json_decode($response, true);
        return $data ?? ['raw' => $response];
    }

    private function generateCacheKey(string $method, string $url, array $params = []): string
    {
        $key = $method . ':' . $url;
        if (!empty($params)) {
            $key .= ':' . md5(json_encode($params));
        }
        return md5($key);
    }
}
