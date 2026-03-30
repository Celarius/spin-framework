<!-- MarkdownTOC list_bullets="*" bracket="round" lowercase="true" autolink="true" indent= depth="4" -->

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
* [2.5. JWT](#25-jwt)
* [2.6. EWT](#26-ewt)

<!-- /MarkdownTOC -->

# 1. Global Helper methods
The following is a list of helper functions available as global functions.

## 1.1. env()
The `env()` function retrieves an environment variable. Variables from a `.env` file at
the project root are auto-loaded at startup and are available alongside OS-level or
process environment variables.
```php
function env(string $var, $default=null)
```
```php
$var = env('ENVIRONMENT');
$host = env('DB_HOST', 'localhost');
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
container('MySuperFunc',
  function() {
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

Helper classes live under the `Spin\Helpers` namespace. All methods are static unless noted otherwise.

## 2.1. ArrayToXml

Converts a PHP associative array to an XML document string.

```php
public function __construct(string $xmlVersion = '1.0', string $xmlEncoding = 'UTF-8')
public function buildXML(array $data, string $startElement = 'data'): string|false
```

Returns the XML as a Base64-encoded string on success, `false` on failure.

**Special key prefixes** control how array keys are mapped to XML:

| Prefix | Effect | Example key |
|--------|--------|-------------|
| `@` | XML attribute on the parent element | `@id` |
| `%` | Text content of the parent element | `%` |
| `#` | CDATA section | `#note` |
| `!` | CDATA section (alternative) | `!note` |

```php
# Basic usage
$xml = (new \Spin\Helpers\ArrayToXml())->buildXML([
    'name' => 'Alice',
    'age'  => 30,
]);

# With XML attributes and CDATA
$xml = (new \Spin\Helpers\ArrayToXml())->buildXML([
    '@id'  => 42,         // becomes <data id="42">
    'name' => 'Alice',
    'bio'  => [
        '#content' => 'Contains <special> & chars',  // wrapped in CDATA
    ],
], 'user');
```

---

## 2.2. Cipher

Encrypts and decrypts strings using OpenSSL. All methods are static.

```php
public static function encrypt(string $data, string $secret = '', string $algorithm = 'AES-256-CBC'): string
public static function decrypt(string $data, string $secret = '', string $algorithm = 'AES-256-CBC'): bool|string
public static function encryptEx(string $data, string $secret, string $cipher = 'aes-256-ctr', string $hashAlgo = 'sha3-512'): string
public static function decryptEx(string $input, string $secret): mixed
public static function getMethods(): array
```

`encrypt` / `decrypt` use a random IV and return Base64-encoded ciphertext.

`encryptEx` / `decryptEx` add an HMAC signature. The token format is:
```
cipher[hashAlgo]:base64(iv).base64(encrypted).base64(hmac)
```
`decryptEx` throws an `\Exception` if the HMAC does not verify.

```php
# Basic encrypt / decrypt
$secret    = env('APP_SECRET');
$encrypted = \Spin\Helpers\Cipher::encrypt('sensitive data', $secret);
$plain     = \Spin\Helpers\Cipher::decrypt($encrypted, $secret);

# Extended encrypt / decrypt (with HMAC verification)
$token  = \Spin\Helpers\Cipher::encryptEx('sensitive data', $secret);
try {
    $plain = \Spin\Helpers\Cipher::decryptEx($token, $secret);
} catch (\Exception $e) {
    // tampered or wrong secret
}

# List available OpenSSL cipher methods
$methods = \Spin\Helpers\Cipher::getMethods();
```

---

## 2.3. Hash

Generates and verifies cryptographic digests using OpenSSL. All methods are static.

```php
public static function generate(string $data, string $method = 'SHA256'): string
public static function check(string $data, string $hash, string $method = 'SHA256'): bool
public static function getMethods(): array
```

`check` uses a timing-safe string comparison to prevent timing attacks.

```php
# Generate a hash
$hash = \Spin\Helpers\Hash::generate('my data');

# Verify a hash
if (\Spin\Helpers\Hash::check('my data', $hash)) {
    // data is unchanged
}

# List available OpenSSL digest methods
$methods = \Spin\Helpers\Hash::getMethods();
```

---

## 2.4. UUID

Generates and validates UUIDs using the Ramsey UUID library. All methods are static.

```php
public static function generate(): string          // v7 — default
public static function v3(string $namespace, string $name): string
public static function v4(): string
public static function v5(string $namespace, string $name): string
public static function v6(): string
public static function v7(): string
public static function is_valid(string $uuid): bool
```

| Version | Algorithm | Use case |
|---------|-----------|----------|
| v3 | Namespace + MD5 | Deterministic from a name |
| v4 | Random | General-purpose unique IDs |
| v5 | Namespace + SHA1 | Deterministic, collision-resistant |
| v6 | Time-based (legacy) | Ordered, backwards-compatible with v1 |
| v7 | Time-based (recommended) | Ordered, sortable, database-friendly |

`generate()` returns a v7 UUID. Prefer v7 for database primary keys (monotonically increasing, index-friendly). Use v4 when ordering is irrelevant.

`is_valid` validates UUIDs of versions v3, v4, and v5.

```php
# Default (v7 — time-ordered, ideal for DB primary keys)
$id = \Spin\Helpers\UUID::generate();

# Random v4
$id = \Spin\Helpers\UUID::v4();

# Deterministic v5 (same namespace + name always yields the same UUID)
$ns = \Spin\Helpers\UUID::v4();   // generate a namespace once, store it
$id = \Spin\Helpers\UUID::v5($ns, 'user@example.com');

# Validate
if (\Spin\Helpers\UUID::is_valid($id)) {
    // valid UUID
}
```

---

## 2.5. JWT

Signs and verifies JSON Web Tokens via the `firebase/php-jwt` library. All methods are static.

```php
public static function encode(array $payload, string $key, string $alg, ?string $keyId = null, ?array $head = null): string
public static function decode(string $jwt, string $key, string $algo = 'HS256'): array
public static function sign(string $msg, string $key, string $alg): string
```

**Supported algorithms:** `HS256`, `HS384`, `HS512`, `RS256`, `RS384`, `RS512`, `ES256`, `ES384`

**Standard payload claims:**

| Claim | Type | Description |
|-------|------|-------------|
| `sub` | string | Subject (e.g. user ID) |
| `iat` | int | Issued-at timestamp |
| `exp` | int | Expiry timestamp |
| `nbf` | int | Not-before timestamp |

```php
$secret = env('APP_SECRET');

# Create a token
$token = \Spin\Helpers\JWT::encode([
    'sub' => (string) $userId,
    'iat' => time(),
    'exp' => time() + 3600,
], $secret, 'HS256');

# Decode and verify
try {
    $payload = \Spin\Helpers\JWT::decode($token, $secret, 'HS256');
    $userId  = $payload['sub'];
} catch (\Exception $e) {
    // invalid signature, expired, or malformed token
}

# Middleware pattern — extract Bearer token and validate
$authHeader = getRequest()->getHeaderLine('Authorization');
if (str_starts_with($authHeader, 'Bearer ')) {
    $token   = substr($authHeader, 7);
    $payload = \Spin\Helpers\JWT::decode($token, $secret, 'HS256');
    container('jwt_claims', $payload);
}
```

---

## 2.6. EWT

EWT (Encrypted Web Token) is a custom token format where the payload is **encrypted** (not just signed). Use EWT instead of JWT when the payload contains sensitive data that must not be readable by the client.

Unlike JWT (which only signs), EWT encrypts the payload with AES and includes an HMAC signature. `decode` returns `null` if signature verification fails rather than throwing.

```php
public static function encode(mixed $data, string $secret, string $hash = 'sha256', string $alg = 'aes-256-ctr', ?string $iv = null): string
public static function decode(string $data, string $secret): string|null
public static function base64url_encode(mixed $input): string
public static function base64url_decode(string $input): mixed
```

Token format: `base64url(header).base64url(encrypted_payload).base64url(hmac_signature)`

```php
$secret = env('APP_SECRET');

# Encrypt a payload
$token = \Spin\Helpers\EWT::encode(['user_id' => 42, 'role' => 'admin'], $secret);

# Decrypt and verify
$payload = \Spin\Helpers\EWT::decode($token, $secret);
if ($payload === null) {
    // tampered token or wrong secret
}
$data = json_decode($payload, true);
```

**JWT vs EWT — when to use which:**

| | JWT | EWT |
|---|-----|-----|
| Payload visible to client? | Yes (Base64-decoded) | No (encrypted) |
| Signature verified? | Yes | Yes (HMAC) |
| Use when | Claims can be public | Claims are sensitive |
| Example | Auth roles, user ID | Internal session data, PII |
