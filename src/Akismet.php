<?php

/*
 * Akismet
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Omines\Akismet;

use Omines\Akismet\API\CheckResponse;
use Omines\Akismet\API\MessageResponse;
use Omines\Akismet\API\VerifyKeyResponse;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Akismet implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const API_BASE_URI = 'https://rest.akismet.com/1.1/';

    private HttpClientInterface $client;
    private ?string $apiKey;
    private ?string $instance;
    private bool $isTesting;

    public function __construct(HttpClientInterface $client, string $apiKey = null, string $instance = null, bool $isTesting = false)
    {
        $this->apiKey = $apiKey;
        $this->instance = $instance;
        $this->isTesting = $isTesting;

        $this->client = $client->withOptions([
           'base_uri' => self::API_BASE_URI,
        ]);
    }

    public function check(AkismetMessage $message): CheckResponse
    {
        return new CheckResponse($this, $this->call('comment-check', $message->getValues()), $this->logger, $message);
    }

    public function submitHam(AkismetMessage $message): MessageResponse
    {
        return new MessageResponse($this, $this->call('submit-ham', $message->getValues()), $this->logger, $message);
    }

    public function submitSpam(AkismetMessage $message): MessageResponse
    {
        return new MessageResponse($this, $this->call('submit-spam', $message->getValues()), $this->logger, $message);
    }

    public function verifyKey(): VerifyKeyResponse
    {
        return new VerifyKeyResponse($this, $this->call('verify-key'), $this->logger);
    }

    public function getClient(): HttpClientInterface
    {
        return $this->client;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getInstance(): ?string
    {
        return $this->instance;
    }

    public function setInstance(string $instance): static
    {
        $this->instance = $instance;

        return $this;
    }

    public function isTesting(): bool
    {
        return $this->isTesting;
    }

    public function setTesting(bool $isTesting): static
    {
        $this->isTesting = $isTesting;

        return $this;
    }

    /**
     * @param array<string, string|string[]> $parameters
     */
    private function call(string $method, array $parameters = []): ResponseInterface
    {
        if (null === $this->apiKey) {
            throw new \LogicException('API key must be set before invoking calls');
        }
        $parameters['api_key'] = $this->apiKey;
        if (null !== $this->instance && !array_key_exists('blog', $parameters)) {
            $parameters['blog'] = $this->instance;
        }
        if ($this->isTesting) {
            $parameters['is_test'] = 'true';
        }

        $options = [
            'body' => $parameters,
        ];
        if (null === $this->logger) {
            $options['on_progress'] = function (int $dlNow, int $dlSize, array $info) use ($method) {
                $this->logger?->debug(sprintf('Akismet %s: Downloaded %s/%s bytes', $method, $dlNow, $dlSize), $info);
            };
        }

        $this->logger?->info(sprintf('Calling %s method on Akismet with %d parameters', $method, count($parameters)), $parameters);

        return $this->client->request('POST', $method, $options);
    }
}
