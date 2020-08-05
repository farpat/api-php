[![Build Status](https://travis-ci.org/farpat/api-php.svg?branch=master)](https://travis-ci.org/farpat/api-php)

# Installation
`composer require farpat/api`


# Use
You can use verb " POST, GET PUT, PATCH and DELETE ". Here are some examples of use to understand functionnalities:

```php
use Farpat\Api\Api;

$users = (new Api)
    ->setPathToCertificat('/path/to/certificat')
    ->setToken('your_token', 'BEARER')
    ->setUserPassword('username', 'password')
    ->get('https://my-site.com/users');
/*
equivalent to: 
curl GET https://my-site.com/users
--cert /path/to/certificat
-H "Authorization: BEARER your_token"
-u "username:password"
*/

$posts = (new Api)
    ->setUrl('https://jsonplaceholder.typicode.com/comments')
    ->get(null, ['postId' => 2]);
/*
equivalent to: 
curl GET https://jsonplaceholder.typicode.com/comments?postId=2
*/

$data = ['data-key-1' => 'data-value-1', 'data-key-2' => 'data-value-2'];
$headers = ['Header-1' => 'header-value-1', 'Header-2' => 'header-value-2'];
$post = (new Api)
    ->setUrl('https://jsonplaceholder.typicode.com')
    ->put('/posts/1', $data, $headers);
/*
equivalent to:
CURL PUT https://jsonplaceholder.typicode.com
-d '{"data-key-1": "data-value-1", "data-key-2": "data-value-2"}'
-H  "Header-1: header-value-1"
-H  "Header-2: header-value-2"
*/
```
