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

    protected function getContent(): string
    {
        return $this->httpResponse->getContent();
    }
}
