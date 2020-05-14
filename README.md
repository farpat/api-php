[![Build Status](https://travis-ci.org/farpat/api-php.svg?branch=master)](https://travis-ci.org/farpat/api-php)

# Installation
`composer require farpat/api`


# Use
You can use verb " POST, GET PUT, PATCH and DELETE ". Here are some examples of use :

```php
$users = (new Api())->setPathToCertificat('/path/to/certificat')->setToken('your_token')->get('users');

$posts = (new Api())->setUrl('https://jsonplaceholder.typicode.com')->get('posts');

$post = (new Api())->setUrl('https://jsonplaceholder.typicode.com')->put('posts/1', $data, $headers);
```
