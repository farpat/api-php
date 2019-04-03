<?php

namespace Tests\Unit;

use Farrugia\Api\Api;
use Farrugia\Api\ApiException;
use Farrugia\Api\CurlException;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ApiTest extends TestCase
{

    /**
     * @var Api
     */
    private $api;

    /** @test */
    public function get_with_wrong_url ()
    {
        $this->expectException(CurlException::class);
        $this->api
            ->setUrl('httpd//jsonplaceholder.typicode.com')
            ->get('posts/1');
    }

    /** @test */
    public function get_data ()
    {
        $data = $this->api
            ->setUrl('https://jsonplaceholder.typicode.com')
            ->get('posts/1');

        $this->assertInstanceOf(\stdClass::class, $data);
        $this->assertNotEmpty($data);
        $this->assertIsInt($data->userId);

        $data = $this->api->get('posts');
        $this->assertNotEmpty((array)$data);

        $data = $this->api->get('comments?postId=1');
        $this->assertNotEmpty((array)$data);

        $data = $this->api->get('comments?postId=fake');
        $this->assertEmpty((array)$data);
    }

    /** @test */
    public function post_data_with_wrong_url ()
    {
        $this->expectException(CurlException::class);
        $this->api
            ->setUrl('httpsd//jsonplaceholder.typicode.com')
            ->post('posts', [
                "title" => 'foo',
                "body" => 'bar',
                "userId" => 1
            ]);
    }

    /** @test */
    public function postData ()
    {
        $data = $this->api
            ->setUrl('https://jsonplaceholder.typicode.com')
            ->post('posts', [
                "title" => 'foo',
                "body" => 'bar',
                "userId" => 1
            ]);
        $this->assertNotEmpty($data);
        $this->assertEquals('foo', $data->title);
    }

    /** @test */
    public function put_data_with_wrong_url ()
    {
        $this->expectException(CurlException::class);
        $this->api
            ->setUrl('httpsd//jsonplaceholder.typicode.com/')
            ->put('posts/1', [
                "id" => 1,
                "title" => 'foo',
                "body" => 'bar',
                "userId" => 1
            ]);
    }

    /** @test */
    public function put_data ()
    {
        $oldTitle = $this->api
            ->setUrl('https://jsonplaceholder.typicode.com/')
            ->get('posts/1')->title;

        $data = $this->api
            ->put('posts/1', [
                "id" => 1,
                "title" => 'toto',
                "body" => 'bar',
                "userId" => 1
            ]);
        $this->assertNotEmpty($data);
        $this->assertEquals('toto', $data->title);
        $this->assertNotEquals($oldTitle, $data->title);
    }

    /** @test */
    public function delete_data ()
    {
        $data = $this->api
            ->setUrl('https://jsonplaceholder.typicode.com/')
            ->delete('posts/1');
        $this->assertEmpty((array)$data);
    }

    /** @test */
    public function test_set_incorrect_path_to_certificat ()
    {
        $this->expectException(ApiException::class);

        $api = new Api();
        $api->setPathToCertificat('incorrect_path');
    }

    /** @test */
    public function test_set_correct_path_to_certificat ()
    {
        $api = new Api();

        $reflectionApi = new \ReflectionClass(Api::class);

        $pathToCertificatProperty = $reflectionApi->getProperty('pathToCertificat');
        $pathToCertificatProperty->setAccessible(true);
        $pathToCertificatProperty->setValue($api, 'correct_path');

        $method = $reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($api, ['GET', [], []]);

        $this->assertEquals('correct_path', $options[CURLOPT_CAINFO]);
    }

    /** @test */
    public function test_set_token ()
    {
        $api = new Api();

        $api->setToken('token', 'bearer');

        $reflectionApi = new ReflectionClass(Api::class);
        $method = $reflectionApi->getMethod('generateOptions');
        $method->setAccessible(true);

        $options = $method->invokeArgs($api, ['GET', [], []]);

        $this->assertTrue(in_array('Authorization: BEARER token', $options[CURLOPT_HEADER]));
    }

    public function setUp (): void
    {
        parent::setUp();
        $this->api = new Api();
    }
}
