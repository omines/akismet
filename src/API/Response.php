<?php

/*
 * Akismet
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Omines\Akismet\API;

use Omines\Akismet\Akismet;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class Response
{
    public function __construct(protected readonly Akismet $akismet,
        protected readonly ResponseInterface $httpResponse,
        protected readonly ?LoggerInterface $logger)
    {
    }

    public function getAkismet(): Akismet
    {
        return $this->akismet;
    }

    /**
     * @return array<string, string[]>
     */
    protected function getHeaders(): array
    {
        return $this->httpResponse->getHeaders();
    }

    protected function getContent(): string
    {
        $headers = $this->getHeaders();

        // Documentation is ambiguous on which of these headers will be filled for errors
        if (array_key_exists('x-akismet-error', $headers)) {
            throw new \RuntimeException($headers['x-akismet-error'][0]); // @codeCoverageIgnore
        } elseif (array_key_exists('x-akismet-alert-msg', $headers)) {
            throw new \RuntimeException($headers['x-akismet-alert-msg'][0]); // @codeCoverageIgnore
        }

        return $this->httpResponse->getContent();
    }
}
