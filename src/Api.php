<?php

namespace Farpat\Api;


class Api
{
    private $url = '/';

    /**
     * @var string|null
     */
    private $pathToCertificat = null;

    /**
     * @var array|null
     * [type, token]
     */
    private $token = null;

    /**
     * @var array|null
     * [user, password]
     */
    private $userpwd = null;

    /**
     * @throws CurlException
     */
    public function post(?string $endPoint, array $data = [], array $headers = [])
    {
        return $this->api($endPoint, 'POST', $data, $headers);
    }

    private function api(?string $endPoint, string $method, array $data, array $headers)
    {
        $ch = curl_init();

        $url = $this->url . ($endPoint ?? '');
        if ($url['-1'] === '/') {
            $url = substr($url, 0, -1);
        }
        curl_setopt_array($ch, $this->generateOptions($url, $method, $data, $headers));

        if (!($response = curl_exec($ch))) {
            throw new CurlException(curl_error($ch), curl_errno($ch));
        }

        curl_close($ch);

        return json_decode($response);
    }

    private function generateOptions(string $url, string $method, array $data, array $headers): array
    {
        $headersInCurl = [];

        if (!empty($headers)) {
            foreach ($headers as $headerKey => $headerValue) {
                $headersInCurl[] = "$headerKey: $headerValue";
            }
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headersInCurl,
            CURLOPT_URL            => $url
        ];

        switch ($method) {
            case 'GET':
            case 'DELETE':
                if (!empty($data)) {
                    $options[CURLOPT_URL] .= '?' . http_build_query($data);
                }
                $options[CURLOPT_CUSTOMREQUEST] = $method;
                break;
            case 'POST':
            case 'PATCH':
            case 'PUT':
                if (is_array($data)) {
                    $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
                    $options[CURLOPT_POSTFIELDS] = json_encode($data);
                } else {
                    $options[CURLOPT_POSTFIELDS] = $data;
                }
                break;
        }

        if (!is_null($this->token)) {
            [$type, $token] = $this->token;
            $options[CURLOPT_HTTPHEADER][] = "Authorization: $type $token";
        }

        if (!is_null($this->userpwd)) {
            [$username, $password] = $this->userpwd;
            $options[CURLOPT_USERPWD] = "$username:$password";
        }

        if (!is_null($this->pathToCertificat)) {
            $options[CURLOPT_CAINFO] = $this->pathToCertificat;
        } else {
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
            $options[CURLOPT_SSL_VERIFYPEER] = 0;
        }

        return $options;
    }

    /**
     * @throws CurlException
     */
    public function put(?string $endPoint, $data, array $headers = [])
    {
        return $this->api($endPoint, 'PUT', $data, $headers);
    }

    /**
     * @throws CurlException
     */
    public function patch(?string $endPoint, array $data = [], array $headers = [])
    {
        return $this->api($endPoint, 'PATCH', $data, $headers);
    }

    /**
     * @throws CurlException
     */
    public function get(?string $endpoint = null, array $data = [], array $headers = [])
    {
        return $this->api($endpoint, 'GET', $data, $headers);
    }

    /**
     * @throws CurlException
     */
    public function delete(?string $endpoint = null, array $data = [], array $headers = [])
    {
        return $this->api($endpoint, 'DELETE', $data, $headers);
    }

    /**
     * @param string $url
     *
     * @return Api
     */
    public function setUrl(string $url): self
    {
        $this->url = $url[-1] === '/' ? $url : $url . '/';
        return $this;
    }

    /**
     * @param string|null $pathToCertificat path/to/cert.cer
     *
     * @return Api
     * @throws ApiException
     */
    public function setPathToCertificat(string $pathToCertificat): self
    {
        if (!is_null($pathToCertificat) && !is_file($pathToCertificat)) {
            throw new ApiException('The certificat path << ' . $pathToCertificat . ' >> does not exist!');
        }

        $this->pathToCertificat = $pathToCertificat;
        return $this;
    }

    public function setUserPassword(string $username, string $password)
    {
        $this->userpwd = [$username, $password];
        return $this;
    }

    public function setToken(string $token, string $type = 'BASIC'): self
    {
        $this->token = [strtoupper($type), $token];
        return $this;
    }
}
