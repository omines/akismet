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

class ActivityResponse extends Response
{
    private bool $parsed = false;

    private int $limit;
    private int $offset;
    private int $total;

    /**
     * @var array<string, array<int, array{site: string, api_calls: string, spam: string, ham: string, missed_spam: string, false_positives: string, is_revoked: bool}>>
     */
    private array $months;

    public function getLimit(): int
    {
        $this->parse();

        return $this->limit;
    }

    public function getOffset(): int
    {
        $this->parse();

        return $this->offset;
    }

    public function getTotal(): int
    {
        $this->parse();

        return $this->total;
    }

    /**
     * @return array<string, array<int, array{site: string, api_calls: string, spam: string, ham: string, missed_spam: string, false_positives: string, is_revoked: bool}>>
     */
    public function getMonths(): array
    {
        return $this->months;
    }

    private function parse(): void
    {
        if (!$this->parsed) {
            if (!is_array($decoded = \json_decode($this->getContent(), true))) {
                throw new \LogicException('Malformed response to Akismet key/site activity call'); // @codeCoverageIgnore
            }

            $this->limit = intval($decoded['limit']);
            $this->offset = intval($decoded['offset']);
            $this->total = intval($decoded['total']);

            $this->months = array_filter($decoded, fn ($k) => preg_match('#^[0-9]{4}\-[0-9]{2}$#', $k), ARRAY_FILTER_USE_KEY);
            $this->parsed = true;
        }
    }
}
