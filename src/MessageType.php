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

enum MessageType: string
{
    case COMMENT = 'comment';
    case FORUM_POST = 'forum-post';
    case REPLY = 'reply';
    case BLOG_POST = 'blog-post';
    case CONTACT_FORM = 'contact-form';
    case SIGNUP = 'signup';
    case MESSAGE = 'message';
}
