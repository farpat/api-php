<?php

namespace Farpat\Api;


use stdClass;

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
     * @param string $endPoint
     * @param array $data
     *
     * @param array $headers
     *
     * @return stdClass|array|null
     * @throws CurlException|ApiException
     */
    public function post(string $endPoint, array $data, array $headers = [])
    {
        return $this->api($endPoint, 'POST', $data, $headers);
    }

    /**
     * @return stdClass|array|null
     */
    private function api(string $endPoint, string $method, array $data = [], array $headers = [])
    {
        $ch = curl_init($this->url . $endPoint);

        curl_setopt_array($ch, $this->generateOptions($this->url . $endPoint, $method, $data, $headers));

        if (!($response = curl_exec($ch))) {
            throw new CurlException(curl_error($ch), curl_errno($ch));
        }

        curl_close($ch);

        $data = json_decode($response);

        if (empty($data)) {
            return null;
        }

        return $data;
    }

    private function generateOptions(string $url, string $method, array $data, array $headers): array
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_URL            => $url
        ];

        switch ($method) {
            case 'GET':
            case 'DELETE':
                if (!empty($data)) {
                    $options[CURLOPT_URL] = '?' . http_build_query($data);
                }
                $options[CURLOPT_CUSTOMREQUEST] = $method;
                break;
            case 'POST':
            case 'PATCH':
            case 'PUT':
                $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
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
     * @param string $endPoint
     * @param array $data
     *
     * @param array $headers
     *
     * @return stdClass|array|null
     * @throws CurlException|ApiException
     */
    public function put(string $endPoint, array $data, array $headers = [])
    {
        return $this->api($endPoint, 'PUT', $data, $headers);
    }

    /**
     * @param string $endPoint
     * @param array $data
     *
     * @param array $headers
     *
     * @return stdClass|array|null
     * @throws CurlException|ApiException
     */
    public function patch(string $endPoint, array $data, array $headers = [])
    {
        return $this->api($endPoint, 'PATCH', $data, $headers);
    }

    /**
     * @param string $endpoint
     *
     * @param array $headers
     *
     * @return stdClass|array|null
     * @throws ApiException
     * @throws CurlException
     */
    public function get(string $endpoint, array $data, array $headers = [])
    {
        return $this->api($endpoint, 'GET', $data, $headers);
    }

    public function delete(string $endpoint, array $data, array $headers = [])
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
    public function setPathToCertificat(?string $pathToCertificat): self
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
    }

    public function setToken(string $token, string $type = 'BASIC'): self
    {
        $this->token = [strtoupper($type), $token];
        return $this;
    }
}
