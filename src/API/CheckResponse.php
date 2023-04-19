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

class CheckResponse extends MessageResponse
{
    private const PRO_TIP = 'x-akismet-pro-tip';

    public function isSpam(): bool
    {
        return 'true' === $this->getContent();
    }

    public function shouldDiscard(): bool
    {
        $headers = $this->httpResponse->getHeaders();

        return array_key_exists(self::PRO_TIP, $headers) && in_array('discard', $headers[self::PRO_TIP], true);
    }
}
