<?php

namespace Tests\Unit;

use Farpat\Api\Api;
use Farpat\Api\ApiException;
use Farpat\Api\CurlException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ApiTest extends TestCase
{
    private ?ReflectionClass $reflectionApi = null;

    /** @test */
    public function get_with_wrong_url()
    {
        $this->expectException(CurlException::class);
        (new Api)
            ->setUrl('httpd//jsonplaceholder.typicode.com')
            ->get('posts/1');
    }

    /** @test */
    public function get_data()
    {
        $data = (new Api)
            ->setUrl('https://jsonplaceholder.typicode.com')
            ->get('posts/3');

        $this->assertInstanceOf(\stdClass::class, $data);
        $this->assertEquals(3, $data->id);

        $data = (new Api)
            ->setUrl('https://jsonplaceholder.typicode.com')
            ->get('/posts/3');

        $this->assertInstanceOf(\stdClass::class, $data);

        $data = (new Api)
            ->setUrl('https://jsonplaceholder.typicode.com/')
            ->get('/posts/3');

        $this->assertInstanceOf(\stdClass::class, $data);

        $data = (new Api)
            ->setUrl('https://jsonplaceholder.typicode.com/posts')
            ->get();

        $this->assertNotEmpty($data);

        $data = (new Api)->setUrl('https://jsonplaceholder.typicode.com/')->get('posts');
        $this->assertNotEmpty($data);

        $data = (new Api)->setUrl('https://jsonplaceholder.typicode.com')->get('comments', ['postId' => 2]);
        $this->assertEquals(2, $data[0]->postId);

        $data = (new Api)->setUrl('https://jsonplaceholder.typicode.com')->get('comments?postId=fake');
        $this->assertEmpty((array)$data);
    }

    /** @test */
    public function post_data_with_wrong_url()
    {
        $this->expectException(CurlException::class);
        (new Api)
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
        $data = (new Api)
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
        $api = new Api;
        $method = $this->reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($api,
            ['url', 'POST', [], ['Accept' => 'application/json', 'Accept-Language' => 'en_US']]);

        $this->assertTrue(in_array('Accept: application/json', $options[CURLOPT_HTTPHEADER]));
        $this->assertTrue(in_array('Accept-Language: en_US', $options[CURLOPT_HTTPHEADER]));
    }

    /** @test */
    public function post_data_with_data_()
    {
        $api = new Api;

        $method = $this->reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($api, ['url', 'POST', ['titi' => 'toto'], []]);

        $this->assertEquals(json_encode(['titi' => 'toto']), $options[CURLOPT_POSTFIELDS]);
    }


    /** @test */
    public function put_data_with_wrong_url()
    {
        $this->expectException(CurlException::class);
        (new Api)
            ->setUrl('httpsd//jsonplaceholder.typicode.com/')
            ->put('posts/1', ["id" => 1, "title" => 'foo', "body" => 'bar', "userId" => 1]);
    }

    /** @test */
    public function put_data()
    {
        $data = (new Api)
            ->setUrl('https://jsonplaceholder.typicode.com/')
            ->put('posts/1', ["id" => 1, "title" => 'toto', "body" => 'bar', "userId" => 1]);

        $this->assertInstanceOf(\stdClass::class, $data);
    }

    /** @test */
    public function delete_data()
    {
        $data = (new Api)
            ->setUrl('https://jsonplaceholder.typicode.com/')
            ->delete('posts/1');
        $this->assertEmpty((array)$data);
    }

    /** @test */
    public function set_incorrect_path_to_certificat()
    {
        $this->expectException(ApiException::class);

        (new Api)->setPathToCertificat('incorrect_path');
    }

    /** @test */
    public function set_correct_path_to_certificat()
    {
        $api = new Api;
        $reflectionApi = new \ReflectionClass(Api::class);

        $pathToCertificatProperty = $reflectionApi->getProperty('pathToCertificat');
        $pathToCertificatProperty->setAccessible(true);
        $pathToCertificatProperty->setValue($api, 'correct_path');

        $method = $reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($api, ['url', 'GET', [], []]);

        $this->assertEquals('correct_path', $options[CURLOPT_CAINFO]);
    }

    /** @test */
    public function set_userpassword()
    {
        $api = new Api;

        $api->setUserPassword('username', 'password');

        $method = $this->reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($api, ['url', 'GET', [], []]);

        $this->assertEquals('username:password', $options[CURLOPT_USERPWD]);
    }

    /** @test */
    public function set_token()
    {
        $api = new Api;
        $api->setToken('token', 'bearer');

        $method = $this->reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($api, ['url', 'GET', [], []]);

        $this->assertTrue(in_array('Authorization: BEARER token', $options[CURLOPT_HTTPHEADER]));
    }

    /** @test */
    public function no_set_path_to_certificat()
    {
        $api = new Api;
        $reflectionApi = new ReflectionClass(Api::class);
        $method = $reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($api, ['url', 'GET', [], []]);

        $this->assertEquals(0, $options[CURLOPT_SSL_VERIFYHOST]);
        $this->assertEquals(0, $options[CURLOPT_SSL_VERIFYPEER]);
    }

    /** @test */
    public function no_set_token()
    {
        $api = new Api;

        $method = $this->reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($api, ['url', 'GET', [], []]);

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
        if ($this->reflectionApi === null) {
            $this->reflectionApi = new ReflectionClass(Api::class);
        }
    }
}
