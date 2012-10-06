# Getting Started

## Installation via _Composer_
**NOTE:** Currently, only installation via [Composer](http://getcomposer.org/) is supported.

If you are not familiar with how to work with [Composer](http://getcomposer.org/) packages, have a look at their very straightforward and comprehensive [Getting Started](http://getcomposer.org/doc/00-intro.md) guide.

You can find the [Packagist](https://packagist.org) page for Spore [here](https://packagist.org/packages/dannykopping/spore).

&nbsp;

In your `composer.json` file, add:

```json
{
	"require": {
		"dannykopping/spore": "dev-master"
	}
}
```

After you've done this, run `php composer.phar update` and *Composer* will download the latest **Spore** to your `vendors` folder.

&nbsp;

---

#### How does Spore work? (FYI)
**Spore** is an extension library built on top of [Slim Framework](http://slimframework.com/). What this means is that **Spore** requires Slim to operate, and this and all other dependencies are automatically downloaded for you by *Composer*.

**Spore** works by leveraging the basic HTTP features and adding some extra functionality on top of it. **Spore** does not need any modifications to the canonical Slim library, but rather overrides a couple operations or properties at runtime.

If you've used [Slim Framework](http://slimframework.com) before, you'll be able to do everything you've done before, except with **Spore** you'll simply use the `\Spore\Spore` class instead of the `\Slim\Slim` class.

&nbsp;

---

## Hello Spore!
**Build your first Spore application**

&nbsp;

Create a new **PHP** page and use the following code:

```php
<?php
	require_once "vendor/autoload.php";

	use Spore\Spore;

	$app = new Spore();
	$app->get("/", function ()
	{
		return array("message" => "Hello World from Spore");
	});

	$app->run();
```

If you're using Apache webserver, add this to your `.htaccess` file or create one:

```apache
RewriteEngine On

# Some hosts may require you to use the `RewriteBase` directive.
# If you need to use the `RewriteBase` directive, it should be the
# absolute physical path to the directory that contains this htaccess file.
#
# RewriteBase /

RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```

Now, navigate to your **PHP** page in your browser and you will see the following output:

```json
{"message":"Hello World from Spore"}
```

---

## Using the examples

In your project, open the file `TestService.php` located under `/vendor/dannykopping/spore/Spore/Services`.

Have a look at the first function in the class:

```php
/**
 * @url			/example1
 * @verbs		GET
 */
public function example1()
{
	return array("some" => "complex", "data" => "in an array",
					"with" => array("nesting"));
}
```

Before we deconstruct this, navigate to `http://path/to/spore/example1` and observe what happens… the URL returns some JSON:

```json
{
    "some": "complex",
    "data": "in an array",
    "with": [
        "nesting"
    ]
}
```

Notice how it reflects what we **returned** from the function `TestService->example1()`:

```php
return array("some" => "complex", "data" => "in an array",
					"with" => array("nesting"));
```

So, how did **Spore** know to route the request URL `/example1` to this function and respond to the `GET` verb?

```php
/**
 * @url			/example1
 * @verbs		GET
 */
```
In **Spore**, you can simply annotate your functions with ReSTful metadata (such as `@url`, `@verbs` and `@auth`). You can find out more about **Routing and Annotations** in the next chapter.

#### Explore!
There are 8 examples in `TestService.php` which show off some of the things you can do with Spore. Explore, have fun and contribute some of your own ;)

###Feel like diving deeper?
Head over to the **next chapter**