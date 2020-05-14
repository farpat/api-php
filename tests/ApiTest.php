<?php

namespace Tests\Unit;

use Farpat\Api\Api;
use Farpat\Api\ApiException;
use Farpat\Api\CurlException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ApiTest extends TestCase
{
    private $api;
    /**
     * @var ReflectionClass
     */
    private $reflectionApi = null;

    /** @test */
    public function get_with_wrong_url()
    {
        $this->expectException(CurlException::class);
        $this->api
            ->setUrl('httpd//jsonplaceholder.typicode.com')
            ->get('posts/1');
    }

    /** @test */
    public function get_data()
    {
        $data = $this->api
            ->setUrl('https://jsonplaceholder.typicode.com')
            ->get('posts/3');

        $this->assertInstanceOf(\stdClass::class, $data);
        $this->assertEquals(3, $data->id);

        $data = $this->api->get('posts');
        $this->assertNotEmpty($data);

        $data = $this->api->get('comments', ['postId' => 2]);
        $this->assertEquals(2, $data[0]->postId);

        $data = $this->api->get('comments?postId=fake');
        $this->assertEmpty((array)$data);
    }

    /** @test */
    public function post_data_with_wrong_url()
    {
        $this->expectException(CurlException::class);
        $this->api
            ->setUrl('httpsd//jsonplaceholder.typicode.com')
            ->post('posts', [
                "title"  => 'foo',
                "body"   => 'bar',
                "userId" => 1
            ]);
    }

    /** @test */
    public function post_data()
    {
        $data = $this->api
            ->setUrl('https://jsonplaceholder.typicode.com')
            ->post('posts', [
                "title"  => 'foo',
                "body"   => 'bar',
                "userId" => 1
            ]);
        $this->assertNotEmpty($data);
        $this->assertEquals('foo', $data->title);
    }

    /** @test */
    public function post_data_with_headers()
    {
        $method = $this->reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($this->api,
            ['url', 'POST', [], ['Accept' => 'application/json', 'Accept-Language' => 'en_US']]);

        $this->assertTrue(in_array('Accept: application/json', $options[CURLOPT_HTTPHEADER]));
        $this->assertTrue(in_array('Accept-Language: en_US', $options[CURLOPT_HTTPHEADER]));
    }

    /** @test */
    public function put_data_with_wrong_url()
    {
        $this->expectException(CurlException::class);
        $this->api
            ->setUrl('httpsd//jsonplaceholder.typicode.com/')
            ->put('posts/1', ["id" => 1, "title" => 'foo', "body" => 'bar', "userId" => 1]);
    }

    /** @test */
    public function put_data()
    {
        $data = $this->api
            ->setUrl('https://jsonplaceholder.typicode.com/')
            ->put('posts/1', ["id" => 1, "title" => 'toto', "body" => 'bar', "userId" => 1]);

        $this->assertInstanceOf(\stdClass::class, $data);
    }

    /** @test */
    public function delete_data()
    {
        $data = $this->api
            ->setUrl('https://jsonplaceholder.typicode.com/')
            ->delete('posts/1');
        $this->assertEmpty((array)$data);
    }

    /** @test */
    public function set_incorrect_path_to_certificat()
    {
        $this->expectException(ApiException::class);

        $this->api->setPathToCertificat('incorrect_path');
    }

    /** @test */
    public function set_correct_path_to_certificat()
    {
        $reflectionApi = new \ReflectionClass(Api::class);

        $pathToCertificatProperty = $reflectionApi->getProperty('pathToCertificat');
        $pathToCertificatProperty->setAccessible(true);
        $pathToCertificatProperty->setValue($this->api, 'correct_path');

        $method = $reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($this->api, ['url', 'GET', [], []]);

        $this->assertEquals('correct_path', $options[CURLOPT_CAINFO]);
    }

    /** @test */
    public function set_userpassword()
    {
        $this->api->setUserPassword('username', 'password');

        $method = $this->reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($this->api, ['url', 'GET', [], []]);

        $this->assertEquals('username:password', $options[CURLOPT_USERPWD]);
    }

    /** @test */
    public function set_token()
    {
        $this->api->setToken('token', 'bearer');

        $method = $this->reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($this->api, ['url', 'GET', [], []]);

        $this->assertTrue(in_array('Authorization: BEARER token', $options[CURLOPT_HTTPHEADER]));
    }

    /** @test */
    public function no_set_path_to_certificat()
    {
        $reflectionApi = new ReflectionClass(Api::class);
        $method = $reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($this->api, ['url', 'GET', [], []]);

        $this->assertEquals(0, $options[CURLOPT_SSL_VERIFYHOST]);
        $this->assertEquals(0, $options[CURLOPT_SSL_VERIFYPEER]);
    }

    /** @test */
    public function no_set_token()
    {
        $method = $this->reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($this->api, ['url', 'GET', [], []]);

        $authorizationHeaderIsPresent = false;
        foreach ($options[CURLOPT_HTTPHEADER] as $header) {
            if (strpos($header, 'Authorization:') !== false) {
                $authorizationHeaderIsPresent = true;
                break;
            }
        }

        $this->assertFalse($authorizationHeaderIsPresent);
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->api = new Api();
        if ($this->reflectionApi === null) {
            $this->reflectionApi = new ReflectionClass(Api::class);
        }
    }
}
