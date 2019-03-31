<?php

namespace Tests\Unit;

use Exception;
use Farrugia\Api\Api;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{

    /**
     * @var Api
     */
    private $api;

    /** @test */
    public function get_with_wrong_url ()
    {
        $this->expectException(Exception::class);
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
    public function postDataWithWrongUrl ()
    {
        $this->expectException(Exception::class);
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
    public function putDataWithWrongUrl ()
    {
        $this->expectException(Exception::class);
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
    public function putData ()
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
    public function deleteData ()
    {
        $data = $this->api
            ->setUrl('https://jsonplaceholder.typicode.com/')
            ->delete('posts/1');
        $this->assertEmpty((array)$data);
    }

    public function setUp (): void
    {
        parent::setUp();
        $this->api = new Api();
    }
}
