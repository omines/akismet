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

class VerifyKeyResponse extends Response
{
    public function isValid(): bool
    {
        return 'valid' === $this->httpResponse->getContent();
    }
}
