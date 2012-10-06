# Getting Started

## Installation via _Composer_
**NOTE:** Currently, only installation via [Composer](http://getcomposer.org/) is supported.

If you are not familiar with how to work with [Composer](http://getcomposer.org/) packages, have a look at their very straightforward and comprehensive [Getting Started](http://getcomposer.org/doc/00-intro.md) guide.

You can find the [Packagist](https://packagist.org) page for Spore [here](https://packagist.org/packages/dannykopping/spore).

&nbsp;

In your `composer.json` file, add:

```json
...
    "require": {  "dannykopping/spore": "dev-master"  }
...
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

```
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
