<?php

namespace Farrugia\Api;


use Exception;
use stdClass;

class Api
{
    private $secret = null;
    private $url = '';

    /**
     * @param string $endPoint
     * @param array $data
     *
     * @param array $headers
     *
     * @return stdClass|array
     * @throws Exception
     */
    public function post (string $endPoint, array $data, array $headers = [])
    {
        return $this->api($endPoint, 'POST', $data, $headers);
    }

    /**
     * @param string $endPoint
     * @param string $method
     * @param array|null $data
     *
     * @param array $headers
     *
     * @return stdClass|array
     * @throws Exception
     */
    private function api (string $endPoint, string $method, array $data = [], array $headers = [])
    {
        $ch = curl_init();

        if ($endPoint[0] !== '/') {
            $endPoint = '/' . $endPoint;
        }

        $options = $this->generateOptions(($this->url ?? '') . $endPoint, $method, $data, $headers);
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error !== '') {
            throw new Exception('CURL ERROR => ' . $error);
        }

        $response = json_decode($response);

        if (empty($response)) {
            return (object)[];
        }

        if (isset($response->error)) {
            throw new Exception('CURL RESPONSE ERROR => ' . $response->error);
        }

        return $response;
    }

    /**
     * @param string $endPoint
     * @param string $method
     * @param array $data
     * @param array $headers
     *
     * @return array
     */
    private function generateOptions (string $endPoint, string $method, array $data, array $headers): array
    {
        $options = [
            CURLOPT_URL => $endPoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers
        ];

        if ($this->secret) {
            $options[CURLOPT_USERPWD] = $this->secret;
            $options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        }

        switch ($method) {
            case 'POST':
            case 'PUT':
                $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            case 'DELETE':
                $options[CURLOPT_CUSTOMREQUEST] = $method;
                break;
        }

        $options[CURLOPT_SSL_VERIFYHOST] = false;
        $options[CURLOPT_SSL_VERIFYPEER] = false;

        return $options;
    }

    /**
     * @param string $endPoint
     * @param array $data
     *
     * @param array $headers
     *
     * @return array|stdClass
     * @throws Exception
     */
    public function put (string $endPoint, array $data, array $headers = [])
    {
        return $this->api($endPoint, 'PUT', $data, $headers);
    }

    /**
     * @param string $endPoint
     * @param array $data
     *
     * @param array $headers
     *
     * @return stdClass
     * @throws Exception
     */
    public function patch (string $endPoint, array $data, array $headers = []): stdClass
    {
        return $this->api($endPoint, 'PATCH', $data, $headers);
    }

    /**
     * @param string $endpoint
     *
     * @return stdClass|array
     * @throws Exception
     */
    public function get (string $endpoint)
    {
        return $this->api($endpoint, 'GET');
    }

    /**
     * @param string $endpoint
     *
     * @return stdClass|array
     * @throws Exception
     */
    public function delete (string $endpoint)
    {
        return $this->api($endpoint, 'DELETE');
    }

    /**
     * @return string|null
     */
    public function getSecret (): ?string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     *
     * @return Api
     */
    public function setSecret (string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl (): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Api
     */
    public function setUrl (string $url): Api
    {
        $this->url = $url[-1] === '/' ? substr($url, 0, -1) : $url;
        return $this;
    }
}
