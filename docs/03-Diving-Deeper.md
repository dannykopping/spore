## Diving Deeper
This section will introduce you to some of the more advanced features of **Spore**.

### Routing and Annotations
One of **Spore**'s key features is its *annotation-based* routing of API requests.

#### What is an annotation?
Well, here's the thing… PHP doesn't **technically** have "annotations" in the traditional sense. However, what it does have is a `Reflection` library and so-called `DocBlock` comments. You know… these guys:

```php
/**
*	I'm a DocBlock (or multiline) comment
*/
```

I have published another `Packagist` library called [**PHP DocBlock Parser Lite**](https://packagist.org/packages/dannykopping/docblock). It's a very simple PHP library that uses the builtin `Reflection` API to parse all the DocBlock comments in a set of given classes.

So… An annotation in the sense I'm using it is as follows:

```php
/**
*	@name			value
*/
```

Straightforward enough, right?
Annotations are a very handy way of adding *metadata* about a block of code, and that's how I've built **Spore**.

#### How do annotations relate to routes?

A **route** is simply a URL that relates to some "resource". In the case of **Spore**, a **route** is defined as a URL which relates to a `callback function` which provides data.

In **Spore**, we call this an ***auto-route***.

Here's an example:

We will use the `@url` annotation to define a **route**, and a `@verbs` annotation to define which HTTP methods (verbs) this callback function will allow.

```php
/**
*	@url			/hello-world
*	@verbs			GET
*/
public function sayHello()
{
	return "Hello World!";
}
```

In **Spore**, **Slim**'s excellent `Router` class is overridden to provide a little extra functionality. When a **Spore** application is started, it will analyze a set of given classes with so-called "auto-routes" (routes with annotations). **Spore** will then know how to relate the URL `http://path/to/spore/hello-world` to the callback function `sayHello()`.

#### Which annotations are available?

<table>
	<tr>
		<th>Annotation</th>
		<th>Description</th>
		<th>Acceptable values</th>
	</tr>
	<tr>
		<td>@url</td>
		<td>A resource URI that relates to a callback function</td>
		<td>Anything you like - provided it complies with <a href="http://docs.slimframework.com/pages/routing-overview/">Slim's URI conventions</a></td>
	</tr>
	<tr>
		<td>@verbs</td>
		<td>A comma-delimited list of acceptable HTTP verbs</td>
		<td>GET, POST, PUT, DELETE and any custom verbs</td>
	</tr>
	<tr>
		<td>@auth</td>
		<td>A comma-delimited list of <a href="#authentication">authentication</a> roles that may access the related callback</td>
		<td>Anything you like</td>
	</tr>
</table>

---

### Serialization
Serialization is a very important feature in **Spore**. **Spore** enables you to forget about ever having to `parse`, `encode` or `decode` the data you're working with.

It's a fundamental premise of **Spore** that data should be kept in its most abstract format (native PHP data types) until it's necessary to encode it into an HTTP response. This allows you to work with your data as **data**, not jumbles of syntax that need to be processed before worked on.

**Spore** will make your life easier by **deserializing incoming request data** and **serializing outgoing response data**, all-the-while respecting content negotiation settings and default content types.

**Spore** leverages **Slim**'s `Middleware` functionality to deserialize incoming request data based on its content type. The **native PHP primatives and objects** will be passed to the receiving auto-route for you to work with. Likewise, when you want to send some data back to the client, simply `return` the data and **Spore** will do the rest.

#### Example
**PHP code**

```php
/**
*	@url		/serialization-example
*	@verbs		POST,PUT
*/
public function serializationExample(Request $request, Response $response)
{
	$incoming = $request->data;
	$outgoing = array("42");
	
	return array("incoming" => $incoming, "outgoing" => $outgoing);
}
```

**HTTP Request**

```http
POST /projects/spore/serialization-example HTTP/1.1
Host: localhost
Content-Length: 64
Origin: chrome-extension://hgmloofddffdnphfgcellkdfbfbjeloo
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.4 (KHTML, like Gecko) Chrome/22.0.1229.79 Safari/537.4
Content-Type: application/json
Accept: */*
Accept-Encoding: gzip,deflate,sdch
Accept-Language: en-US,en;q=0.8
Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3
Cookie: splashShown1.5=1; PHPSESSID=e8d6cce60ba5c3471db80bb6b110400e
Pragma: no-cache
Cache-Control: no-cache

{"question":"What is the meaning of life?", "type":"existential"}
```

**JSON response**

```json
{
    "incoming": {
        "question": "What is the meaning of life?",
        "type": "existential"
    },
    "outgoing": [
        "42"
    ]
}
```

The important elements to note in the example above are:

* The `Content-Type: application/json` header was used to **deserialize** the incoming `JSON` data
* The **auto-route** has two default parameters: `Request $request` and `Response $response` - more on these later
* The `$request->data` property contains the **deserialized** data passed in the body of the HTTP `POST` request
* The data passed back from the `serialization-example` **auto-route** was **serialized** back into `JSON` because this is the default content type and the `Accept: */*` header means we can return whatever content encoding we like.
* If we changed the `Accept` header to `Accept: application/xml` then the `serialization-example` **auto-route** would have given us this:

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<data>
	<incoming>
		<question>What is the meaning of life?</question>
		<type>existential</type>
	</incoming>
	<outgoing>
		<element>42</element>
	</outgoing>
</data>
```

#### Acceptable serialization formats
Below is a table of serialization formats that can be used. For more information on how to use these, see the **Configuration** section of this document.

<table>
	<tr>
		<th>Name</th>
		<th>Content-Type</th>
		<th>Incoming</th>
		<th>Outgoing</th>
	</tr>
	<tr>
		<td>JSON (default)</td>
		<td>application/json</td>
		<td align="center">•</td>
		<td align="center">•</td>
	</tr>
	<tr>
		<td>XML</td>
		<td>application/xml,text/xml</td>
		<td align="center">•</td>
		<td align="center">•</td>
	</tr>
	<tr>
		<td>CSV</td>
		<td>text/csv</td>
		<td align="center">•</td>
		<td align="center"></td>
	</tr>
</table>

#### Overriding serialization
You can override the outgoing serialization mechanism by using `echo` instead of using `return`.

Using the same example as above, here's how you could handle the serialization yourself:

```php
/**
*	@url		/serialization-example
*	@verbs		POST,PUT
*/
public function serializationExample(Request $request, Response $response)
{
	$incoming = $request->data;
	$outgoing = array("42");

	$response->headers["Content-Type"] = "application/json";
	echo json_encode(array("incoming" => $incoming, "outgoing" => $outgoing));
}
```

It is not recommended that you do this though, since your code will become less portable. If you have a function that returns native PHP data, it will be possible to use that code internally (i.e. purely on the back-end without an API) using plain ol' PHP classes, whereas if you use `echo`, this will become a lot more difficult.

Overriding the serialization mechanism will not affect any [authentication](03-Diving-Deeper.md#authentication) rules already in place.

---

### Authentication
Authentication should always be a concern when developing an API. You may want to restrict access to certain administrative functions, or conditional restrictions based on session data.

**Spore** enables you to keep your API **auto-routes** safe by providing the `@auth` annotation and a special **Authentication Callback** mechanism.

Consider the following example:

```php
/**
*	@url		/auth-example
*	@verbs		POST
*   @auth		admin,super-user,Chuck Norris
*/
public function somethingImportant(Request $request, Response $response)
{
	return "Congrats, you're special!";
}
```

Using the `@auth` annotation alone does not secure your **auto-route** - you will need to define an **Authentication Callback** to handle authentication requests.

```php
require_once "vendor/autoload.php";

use Spore\Spore;

$app = new Spore();

$app->setAuthCallback(function ($roles) use ($app)
{
	if(empty($roles))
		return true;

	$currentRole = "Chuck Norris";
	return in_array($currentRole, $roles);
});

$app->run();
```

The `setAuthCallback` function is very simple. All it needs to do is return `true` or `false`. You can define whatever rules you like in order to validate or invalidate the request. A result of `true` means that **Spore** will continue with the request, while a `false` will fire an authentication error.

In the above example, look at the following line:

```php
@auth		admin,super-user,Chuck Norris
```

The `@auth` annotation allows you to define a comma-delimited list of acceptable roles that can access this **auto-route**. A "role" is nothing more than an identifier. In the example above, we will allow any "user" with the "role" of `admin`, `super-user` or `Chuck Norris` to access the function.

If you run the above example, the output will be:

```json
"Congrats, you're special!"
```

… because in the `setAuthCallback` function, we defined our `$currentRole` to be `"Chuck Norris"` and checked to see if it was in the list of acceptable roles (passed into the callback as the `$roles` argument).

If we change the `$currentRole` to be `"Bob"` - you'll see the following output:

```json
{"message":"You are not authorized to execute this function"}
```

---

### Handlers
Shit happens; and we need to handle it elegantly.

If you've used [Slim](http://slimframework.com/) before, you'll know that it has a lovely API for handling [**errors/exceptions** and **not found** errors](http://docs.slimframework.com/pages/error-handling-overview/) rather elegantly.

**Spore** has - by default - custom **error** and **not found** handlers to get you started. I always forget this and [report bugs](http://https://github.com/codeguy/Slim/issues/439) immediately (sorry Josh), but **Slim error and not found handlers** only work if you have **debug mode** turned off.

If you look in the `Spore.php` file, you will see two functions - `errorHandler` and `notFoundHandler`:

```php
public function errorHandler(Exception $e)
{
	$this->contentType($this->config("content-type"));
	$data = Serializer::getSerializedData($this, array(
													  "error" => array(
														  "message" => $e->getMessage(),
														  "code"    => $e->getCode(),
														  "file"    => $e->getFile(),
														  "line"    => $e->getLine(),
													  )
												 ));

	$this->halt(Status::INTERNAL_SERVER_ERROR, $data);
}

public function notFoundHandler()
{
	$this->contentType($this->config("content-type"));
	$data = Serializer::getSerializedData($this, array(
													  "error" => array(
														  "message" => "'" . $this->request()->getResourceUri() . "' could not be resolved to a valid API call",
														  "req"     => $this->request()->getIp()
													  )
												 ));

	$this->halt(Status::NOT_FOUND, $data);
}
```

If you would like to override either of these functions, you can do so like this:

```php
require_once "vendor/autoload.php";

use Spore\Spore;
use Spore\ReST\Model\Status;

$app = new Spore();
$app->setErrorHandler(function(Exception $e) use ($app)
{
	$app->halt(Status::INTERNAL_SERVER_ERROR, "Shit happened ({$e->getMessage()})");
});

$app->setNotFoundHandler(function() use ($app)
{
	$app->halt(Status::NOT_FOUND, "Shit not found.");
});

$app->run();
```

True to form, **Spore** adds a little extra. **Spore** also allows you to define an **authentication error handler** to customize error messages when authentication exceptions occur.

The default `authFailedHandler` is rather simple:

```php
public function authFailedHandler()
{
	$this->contentType($this->config("content-type"));
	$data = Serializer::getSerializedData($this, array(
													  "message" => "You are not authorized to execute this function"
												 ));

	$this->halt(Status::UNAUTHORIZED, $data);
}
```

…and you can define your own if you'd like:

```php
require_once "vendor/autoload.php";

use Spore\Spore;
use Spore\ReST\Model\Status;

$app = new Spore();

$app->setAuthCallback(function($roles) use ($app)
{
	return false;
});

$app->setAuthFailedHandler(function() use ($app)
{
	$app->halt(Status::FORBIDDEN, "Oi! Not so fast, big guy");
});

$app->run();
```

### Request and Response
In every **auto-route**, the two default parameters of every callback function are:
`\Spore\ReST\Model\Request $request` and `\Spore\ReST\Model\Response $response`. These classes are convenience classes meant to help you with common API tasks.

#### Request
The `Request` class will be constructed and passed to the **auto-route** with several useful properties:

<table width="100%">
	<tr>
		<th>Name</th>
		<th>Description</th>
	</tr>
	<tr>
		<td>data</td>
		<td>The deserialized request body</td>
	</tr>
	<tr>
		<td>params</td>
		<td>An associative array of params passed to a Slim route (e.g. <code>/example/:param1/:param2</code>)<br/>
		<code>/example/abc/123</code> results in <code>array("param1" => "abc", "param2" => "123")</code></td>
	</tr>
	<tr>
		<td>queryParams</td>
		<td>An associative array of query string params (e.g. <code>/example?name=danny</code> results in <code>array("name" => "danny")</code>)</td>
	</tr>
</table>

You can combine all 3 of these different request data types. See the example below:

**PHP code**

```php
/**
 * @url			/req-data/:num1+:num2
 * @verbs		POST
 */
public function reqData(Request $request, Response $response)
{
	return $request;
}
```

**HTTP Request**

```http
POST /projects/spore/req-data/123/456?hello=spore HTTP/1.1
Host: localhost
Content-Length: 65
Origin: chrome-extension://hgmloofddffdnphfgcellkdfbfbjeloo
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.4 (KHTML, like Gecko) Chrome/22.0.1229.79 Safari/537.4
Content-Type: application/json
Accept: */*
Accept-Encoding: gzip,deflate,sdch
Accept-Language: en-US,en;q=0.8
Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3
Cookie: splashShown1.5=1; PHPSESSID=e8d6cce60ba5c3471db80bb6b110400e
Pragma: no-cache
Cache-Control: no-cache

{"question":"What is the meaning of life?", "type":"existential"}
```

**JSON Response**

```json
{
    "data": {
        "question": "What is the meaning of life?",
        "type": "existential"
    },
    "queryParams": {
        "hello": "spore"
    },
    "params": {
        "num1": "123",
        "num2": "456"
    }
}
```

The `\Spore\ReST\Model\Request` class also has access to the internal **Slim** `Request` class - and you can access it as follows:

`$request->request()`

#### Response
The `Response` class will be constructed and passed to the **auto-route** with several useful properties:

<table width="100%">
	<tr>
		<th>Name</th>
		<th>Description</th>
	</tr>
	<tr>
		<td>status</td>
		<td>The HTTP status code to return.<br/>See <code>Spore\ReST\Model\Status</code> for a list of appropriate HTTP statuses</td>
	</tr>
	<tr>
		<td>headers</td>
		<td>An associative array of HTTP headers to return</td>
	</tr>
</table>

You can use these properties as follows:

**PHP code**

```php
/**
 * @url			/response-example
 * @verbs		GET
 */
public function responseExample(Request $request, Response $response)
{
	$response->status = Status::PAYMENT_REQUIRED;
	$response->headers["Secret-Code"] = "1234";
	
	return "Greetings";
}
```

**HTTP Response**

```http
HTTP/1.1 402 Payment Required
Date: Sat, 06 Oct 2012 14:13:04 GMT
Server: Apache/2.2.14 (Unix) DAV/2 mod_ssl/2.2.14 OpenSSL/0.9.8l PHP/5.3.1 mod_perl/2.0.4 Perl/v5.10.1
X-Powered-By: PHP/5.3.1
Secret-Code: 1234
Content-Encoding: gzip
Vary: Accept-Encoding
Content-Length: 31
Content-Type: application/json
Expires: 0
Cache-Control: no-cache

"Greetings"
```

The `\Spore\ReST\Model\Response` class also has access to the internal **Slim** `Response` class - and you can access it as follows:

`$response->response()`

## Configuration
**Spore** contains a number of useful, configurable properties.

The **Spore** configuration works off the native Slim `config` functionality. Simply use it as you would normally use the Slim configuration.

See [the Slim documentation](http://docs.slimframework.com/pages/configure-settings/) for more information.

#### Configuration Options
<table width="100%">
	<tr>
		<th>Name</th>
		<th>Description</th>
		<th>Default</th>
		<th>Options</th>
	</tr>
	<tr>
		<td>debug</td>
		<td>Debug mode</td>
		<td><code>true</code></td>
		<td><code>boolean</code></td>
	</tr>
	<tr>
		<td>content-type</td>
		<td>The default content encoding type</td>
		<td><code>application/json</code></td>
		<td>See <a href="#acceptable-serialization-formats">Acceptable serialization formats</a></td>
	</tr>
	<tr>
		<td>gzip</td>
		<td>GZIP compression</td>
		<td><code>true</code></td>
		<td><code>boolean</code></td>
	</tr>
	<tr>
		<td>services</td>
		<td>Path to classes to be analyzed for <em>auto-routing</em></td>
		<td><code>Spore/Services</code> (contains sample classes)</td>
		<td>file path</td>
	</tr>
	<tr>
		<td>services-ns</td>
		<td>The namespace of the classes to be analyzed for <em>auto-routing</em></td>
		<td><code>Spore\Services</code></td>
		<td><code>string</code> or <code>null</code></td>
	</tr>
</table>

Here's an example of how you can override a configuration value:

```php
require_once "vendor/autoload.php";

use Spore\Spore;

$app = new Spore(array("debug" => false));
$app->get("/", function () use ($app)
{
	return array("message" => "Hello World from Spore", "debugModeEnabled" => $app->config("debug"));
});

$app->run();
```