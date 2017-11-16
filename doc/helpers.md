<!-- MarkdownTOC list_bullets="*" bracket="round" lowercase="true" autolink="true" indent="" depth="4" -->

* [1. Global Helper methods](#1-global-helper-methods)
* [1.1. env\(\)](#11-env)
* [1.2. app\(\)](#12-app)
* [1.3. config\(\)](#13-config)
* [1.4. container\(\)](#14-container)
* [1.5. logger\(\)](#15-logger)
* [1.6. db\(\)](#16-db)
* [1.7. cache\(\)](#17-cache)
* [1.8. getRequest\(\)](#18-getrequest)
* [1.9. getResponse\(\)](#19-getresponse)
* [1.10. queryParams\(\)](#110-queryparams)
* [1.11. queryParam\(\)](#111-queryparam)
* [1.12. postParams\(\)](#112-postparams)
* [1.13. postParam\(\)](#113-postparam)
* [1.14. cookieParams\(\)](#114-cookieparams)
* [1.15. cookieParam\(\)](#115-cookieparam)
* [1.16. cookie\(\)](#116-cookie)
* [1.17. redirect\(\)](#117-redirect)
* [1.18. response\(\)](#118-response)
* [1.19. responseJson\(\)](#119-responsejson)
* [1.20. responseXml\(\)](#120-responsexml)
* [1.21. responseHtml\(\)](#121-responsehtml)
* [1.22. responseFile\(\)](#122-responsefile)
* [2. Global Helper Objects](#2-global-helper-objects)
* [2.1. ArrayToXml](#21-arraytoxml)
* [2.2. Cipher](#22-cipher)
* [2.3. Hash](#23-hash)
* [2.4. UUID](#24-uuid)

<!-- /MarkdownTOC -->

# 1. Global Helper methods
The following is a list of helper functions available as global functions.

## 1.1. env()
The `env()` function is used to retreive an environment variable from the underlaying OS.
```php
function env(string $var, $default=null)
```
```php
$var = env('ENVIRONMENT');
```

## 1.2. app()
The `app()` function is used to retreive the instance of the global application variable. The optional `$property` parameter is used to retreive an application property from the $app object.
```php
function app(string $property=null)
```
```php
$val = app('code');
```

## 1.3. config()
The `config()` function is used to retreive the config object, or to get/set a specific config key (or array). If the additional `$value` parameter is given the config `$key` is set to that value.

*Note that the key name is a dot notation. Example: "application.maintenance" gives maintenance key in application array*
```php
function config(string $key=null, string $value=null)
```
```php
$val = config('application.maintenance');
```

## 1.4. container()
The `container()` function is used to retreive the container object, or to get/set a specific container key. 

If the additional `$id` parameter is given the container item with `$key` is returned.

If the additional `$value` parameter is given the container item with `$key` is set to the `$value`. This can be any valid variable, even callables.
```php
function container(string $id=null, $value=null)
```
```php
container('MySuperFunc', function() {
  // do something in the Super Function
}
);

# Call the defined callable
container('MySuperFunc');
```

## 1.5. logger()
The `logger()` function is used to access the (PSR-3)[http://www.php-fig.org/psr/psr-3/] logger object.
```php
function logger()
```
```php
logger()->critical('Something bad has happened', ['details'=>'Encountered mystic radiation']);
```

## 1.6. db()
The `db()` function is used to access one of the defined connections.

*Note: If the $connectionName is not given, the 1st connection in the list of connections is used*
```php
function db(string $connectionName='')
```
```php
$rows = db()->rawQuery('SELECT * FROM table WHERE field = :value',['value'=>123]);
```

## 1.7. cache()
The `cache()` function is used to access one of the defined caches. The caches are shared between connections, regardless of the driver.

*Note: If the $driverName is not given, the 1st connection in the list of connections is used*
```php
function cache(string $driverName='')
```
```php
# Set a value
cache('APCU')->set('key1',1234);

# Get a value
$value = cache('APCU')->get('key1');
```

## 1.8. getRequest()
The `getRequest()` function is used to access the (PSR-7)[http://www.php-fig.org/psr/psr-7/] HTTP ServerRequest object

```php
function getRequest()
```
```php
$request = getRequest();
```

## 1.9. getResponse()
The `getResponse()` function is used to access the (PSR-7)[http://www.php-fig.org/psr/psr-7/] HTTP Response object

```php
function getResponse()
```
```php
$request = getResponse();
```

## 1.10. queryParams()
The `queryParams()` function returns an array with all query parameters (on the URL).

```php
function queryParams()
```
```php
$params = queryParams();
$value =$params['xyz'] ?? '';
```

## 1.11. queryParam()
The `queryParam()` function returns one named query parameter, or null.

```php
function queryParam(string $paramName, $default=null)
```
```php
$value = queryParam('xyz') ?? '';
```

## 1.12. postParams()
The `postParams()` function returns an array with all post parameters. The post params are decoded if they are sent in a post request with the content-type `multipart/form-data`.

```php
function postParams()
```
```php
$params = postParams();
$value =$params['xyz'] ?? '';
```

## 1.13. postParam()
The `postParam()` function returns one named post parameter, or null.

```php
function postParam(string $paramName, $default=null)
```
```php
$value = postParam('xyz') ?? '';
```

## 1.14. cookieParams()
The `cookieParams()` function returns an array with all cookies sent in the request

```php
function cookieParams()
```
```php
$params = cookieParams();
$value =$params['sessionid'] ?? '';
```

## 1.15. cookieParam()
The `cookieParam()` function returns one named cookie, or null.

```php
function cookieParam(string $paramName, $default=null)
```
```php
$value = cookieParam('sessionid') ?? '';
```

## 1.16. cookie()
The `cookie()` function returns one named cookie, or sets a cookie for the next HTTP response.

```php
function cookie(string $name, ?string $value=null, int $expire=0, string $path='', string $domain='', bool $secure=false, bool $httpOnly=false)
```
```php
# Read the value
$value = cookie('sessionid') ?? '';

# Set a Cookie
$value = cookie('sessionid',md5(microtime(true)));

```


## 1.17. redirect()
The `redirect()` sends a redirect response to the user.

```php
function redirect(string $uri, $status=302, $headers = [])
```
```php
# in a controller ...
return redirect('/antoherUrl',301);
```


## 1.18. response()
The `response()` sets the HTTP response to send to the user

```php
function response(string $body='', int $code=200, array $headers=[])
```
```php
# In a controller ...
return response('This is a response');
```

## 1.19. responseJson()
The `responseJson()` converts the $data array to a JSON document and sets it as the response to the user, and also adds `application/json` as a content-type header if not present.

```php
function responseJson(array $data=[], int $code=200, int $options=JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK, array $headers=[])
```
```php
# In a controller ...
$data['result'] = 'OK';
return responseJson($data);
```

## 1.20. responseXml()
The `responseXml()` sets the $xml document as a response to the user, and also adds `application/xml` as a content-type header.

See [ArrayToXml](http://stackoverflow.com/questions/99350/passing-php-associative-arrays-to-and-from-xml) for details.

```php
function responseXml(array $ar=[], int $code=200)
```
```php
# In a controller ...
$data = [
  '@id' => 1,
  'name' => 'Jon Doe',
  'description' => 'bar',
  'phones' => [
    'mobile' => '+358-40-123 1234',
    'work' => '+358-09-123 1234'
  ]
];

return responseXml($data);
```

## 1.21. responseHtml()
The `responseHtml()` creates a response with the $body, and sets the content-type header to `text/html`.

```php
function responseHtml(string $body='', int $code=200, array $headers=[])
```
```php
# In a controller ...
$body = '<html><body><h1>Hi there</h1></body</html>'
return responseHtml($body);
```


## 1.22. responseFile()
The `responseFile()` creates a response with the $filename. Tries to auto-determine the content-type if not supplied in $headers.

```php
function responseFile(string $filename, int $code=200, array $headers=[])
```
```php
# In a controller ...
$filename = 'images/image.png'
return responseFile($filename);
```

---
# 2. Global Helper Objects

## 2.1. ArrayToXml
The `ArrayToXml()` converts an `$array` to a `$xml` document.

```php
$array = [];
$array['key'] = 'value';
# Convert array to XML document
$xml = (new \Spin\Helpers\ArrayToXml())->buildXML($array);
```

## 2.2. Cipher
The `Cipher` helper encrypts/decrypts strings using OpenSSL.

```php
# Encrypt a value
$encryptedValue = \Spin\Helper\Cipher::encrypt( $plain, 'secret', 'AES-256-CBC' );

# Decrypt a value
$plain = \Spin\Helper\Cipher::decrypt( $encryptedValue, 'secret', 'AES-256-CBC' );
```

## 2.3. Hash
The `Hash` helper produces hashes using OpenSSL digest methods.

```php
# Produce a hash
$digest = \Spin\Helper\Hash::generate('This is the data','SHA256');
```

## 2.4. UUID
The `UUID` helper produces UUID v3, v4 and v4 unique UUID's.

```php
# UUIDv4 GUID
$uuidv4 = \Spin\Helper\UUID::generate();

# UUIDv5 GUID
$uuidv5 = \Spin\Helper\UUID::v5($uuidv4,'My v5 UUID');
```
