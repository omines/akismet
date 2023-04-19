# Akismet
[![Latest Stable Version](https://poser.pugx.org/omines/akismet/version)](https://packagist.org/packages/omines/akismet)
[![Total Downloads](https://poser.pugx.org/omines/akismet/downloads)](https://packagist.org/packages/omines/akismet)
[![Latest Unstable Version](https://poser.pugx.org/omines/akismet/v/unstable)](//packagist.org/packages/omines/akismet)
[![License](https://poser.pugx.org/omines/akismet/license)](https://packagist.org/packages/omines/akismet)

This library provides a straightforward object oriented and pluggable implementation of the well known online Akismet
spam detection service.

## Documentation

TBD.

## Using as a Symfony service

Add the following to `config/services.yaml`:

```yaml
    Omines\Akismet\Akismet:
        arguments:
            # Your 'blog', this should be the root URL of your deployment
            $instance: '%env(ROOT_URL)%'

            # Your Akismet API key
            $apiKey: '%env(AKISMET_KEY)%'
```
Next add these 2 parameters to your `.env.local` or wherever else you manage
your environment variables.

## Contributing

Please see [CONTRIBUTING.md](https://github.com/omines/akismet/blob/master/CONTRIBUTING.md) for details.

## Legal

This software was developed for internal use at [Omines Full Service Internetbureau](https://www.omines.nl/)
in Eindhoven, the Netherlands. It is shared with the general public under the permissive MIT license, without
any guarantee of fitness for any particular purpose. Refer to the included `LICENSE` file for more details.
