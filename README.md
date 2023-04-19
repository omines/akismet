# Akismet Client Library for PHP
[![Latest Stable Version](https://poser.pugx.org/omines/akismet/version)](https://packagist.org/packages/omines/akismet)
[![Total Downloads](https://poser.pugx.org/omines/akismet/downloads)](https://packagist.org/packages/omines/akismet)
[![Latest Unstable Version](https://poser.pugx.org/omines/akismet/v/unstable)](//packagist.org/packages/omines/akismet)
[![License](https://poser.pugx.org/omines/akismet/license)](https://packagist.org/packages/omines/akismet)

This library provides a straightforward object oriented and pluggable implementation of the well known online Akismet
spam detection service.

## Documentation

Install the library with Composer:
```shell
composer require omines/akismet
```

### Creating the service

You have to bring your own HTTP client implementation, based on [Symfony Contracts](https://symfony.com/doc/current/components/contracts.html).
Easiest is to install the [Symfony HTTP Client component](https://symfony.com/doc/current/http_client.html):
```shell
composer require symfony/http-client
```
When using the Symfony framework this will provide a configurable service you can inject where needed, otherwise you 
can instantiate a client using:
```php
$httpClient = HttpClient::create();
```
Now instantiate the service class, also providing your [Akismet API key](https://akismet.com/account/) and the root URL
of your site/blog:
```php
$akismet = new Akismet($httpClient, 'Akismet API key', 'https://www.example.org/');
```
### Using as a Symfony service

Add the following to `config/services.yaml`, assuming autowiring is enabled as it should in Symfony:
```yaml
    Omines\Akismet\Akismet:
        arguments:
            # Your 'blog', this should be the root URL of your deployment
            $instance: '%env(ROOT_URL)%'

            # Your Akismet API key
            $apiKey: '%env(AKISMET_KEY)%'
```
Then make sure your relevant `.env` file or the actual environment variables contain correct values for `ROOT_URL`
and `AKISMET_KEY`. You can now inject `Omines\Akismet\Akismet` wherever you need it.

### Creating a message

You have to construct and fill an `AkismetMessage` before you can check or submit anything:
```php
$message = (new AkismetMessage())
    ->setUserIP('1.2.3.4')
    ->setType(MessageType::COMMENT)
    ->setContent('Some spammy message')
    ->setAuthorEmail('medicine_seller_1983@gmail.com')
;
```
Depending on your framework you will already have some useful data available in either a Symfony HTTP Foundation `Request`
instance or a PSR-7 `ServerRequestInterface` derivant. Bootstrapping a message from these to copy the user IP, user agent
and HTTP referrer fields is easy:
```php
$message = AkismetMessage::fromRequest($symfonyRequest);
// or
$message = AkismetMessage::fromPSR7Request($psr7Request);
```
You can, and sometimes should, safely serialize the `AkismetMessage` class, for example when implementing moderation 
queues. After moderation you can unserialize the message to submit the exact message you checked before as Ham or Spam.

### Checking a message

Performing a spam check is then simple:
```php
$response = $akismet->check($message);
if ($response->isSpam()) {
    if ($response->shouldDiscard()) {
      // Akismet believes this to be the most pervasive and worst spam, not even worthy of moderation 
    } else {
      // The message is considered spam, so it should either be refused or manually reviewed
    }
}
```
Read [their blog post](https://akismet.com/blog/theres-a-ninja-in-your-akismet/) on the 'should discard' feature.

### Asynchronous invocation

When using `symfony/http-client` as the HTTP client implementation all calls are asynchronous out of the box. As such,
in the example above, the call to `Akismet::check` is **not** blocking further execution. This allows you to improve
your site performance by continuing to do other tasks in parallel, such as preparing notification emails and such.

Execution will block when you call any informational methods on the response, in this case the `isSpam` method call.

## To Do

This library is currently internally being tested for production use, changes may be necessary, hence the library is
currently in major version 0. It is to be considered stable and safe though, and we will stabilize the major version
once we believe the library API to be sufficiently stable.

Currently on the to do list are:
 * Error handling
 * Submit ham/spam call implementation
 * Diagnostic calls for usage and API limits

If more people start using the library we will at some point also provide a Symfony Bundle and corresponding recipes
for even easier integration.

## Contributing

Contributions are **welcome** and will be credited.

We accept contributions via Pull Requests on [Github](https://github.com/omines/akismet).
Follow [good standards](http://www.phptherightway.com/), keep the [PHPStan level](https://phpstan.org/user-guide/rule-levels) at 9,
and keep the test coverage at 100%.

Before committing, run `bin/prepare-commit` to automatically follow coding standards, run PHPStan and run all tests.

## Legal

This software was developed for internal use at [Omines Full Service Internetbureau](https://www.omines.nl/)
in Eindhoven, the Netherlands. It is shared with the general public under the permissive MIT license, without
any guarantee of fitness for any particular purpose. Refer to the included `LICENSE` file for more details.