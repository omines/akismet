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

class UsageLimitResponse extends Response
{
    private bool $parsed = false;

    private ?int $limit;
    private int $usage;
    private float $percentage;
    private bool $throttled;

    public function getLimit(): ?int
    {
        $this->parse();

        return $this->limit;
    }

    public function getUsage(): int
    {
        $this->parse();

        return $this->usage;
    }

    public function getPercentage(): float
    {
        $this->parse();

        return $this->percentage;
    }

    public function isThrottled(): bool
    {
        $this->parse();

        return $this->throttled;
    }

    private function parse(): void
    {
        if (!$this->parsed) {
            if (!is_array($decoded = \json_decode($this->getContent(), true))) {
                throw new \LogicException('Malformed response to Akismet usage limit call'); // @codeCoverageIgnore
            }
            $this->limit = intval($decoded['limit']) ?: null;
            $this->usage = intval($decoded['usage']);
            $this->percentage = floatval($decoded['percentage']);
            $this->throttled = boolval($decoded['throttled']);
            $this->parsed = true;
        }
    }
}
