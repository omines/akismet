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

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

class AkismetMessage
{
    /** @var array{comment_context: string[]}&string[] */
    private array $values; // @phpstan-ignore-line

    public function __construct()
    {
        $this->values = [
            'comment_context' => [],
        ];
    }

    public function getAuthor(): ?string
    {
        return $this->get('comment_author');
    }

    public function setAuthor(?string $author): static
    {
        return $this->set('comment_author', $author);
    }

    public function getAuthorEmail(): ?string
    {
        return $this->get('comment_author_email');
    }

    public function setAuthorEmail(?string $email): static
    {
        return $this->set('comment_author_email', $email);
    }

    public function getAuthorUrl(): ?string
    {
        return $this->get('comment_author_url');
    }

    public function setAuthorUrl(?string $url): static
    {
        return $this->set('comment_author_url', $url);
    }

    public function getContent(): ?string
    {
        return $this->get('comment_content');
    }

    public function setContent(?string $content): static
    {
        return $this->set('comment_content', $content);
    }

    public function getDateCreated(): ?\DateTimeImmutable
    {
        $date = $this->get('comment_date_gmt');

        return null !== $date ? new \DateTimeImmutable($date) : null;
    }

    public function setDateCreated(?\DateTimeInterface $date): static
    {
        return $this->set('comment_date_gmt', $date?->format('c'));
    }

    public function getDateModified(): ?\DateTimeImmutable
    {
        $date = $this->get('comment_post_modified_gmt');

        return null !== $date ? new \DateTimeImmutable($date) : null;
    }

    public function setDateModified(?\DateTimeInterface $date): static
    {
        return $this->set('comment_post_modified_gmt', $date?->format('c'));
    }

    public function getEncoding(): ?string
    {
        return $this->get('blog_charset');
    }

    public function setEncoding(?string $encoding): static
    {
        return $this->set('blog_charset', $encoding);
    }

    public function clearHoneyPot(): static
    {
        return $this->set('honeypot_field_name', null)->set('hidden_honeypot_field', null);
    }

    public function getHoneyPotFieldName(): ?string
    {
        return $this->get('honeypot_field_name');
    }

    public function getHoneyPotValue(): ?string
    {
        return $this->get('hidden_honeypot_field');
    }

    public function setHoneyPot(string $fieldName, string $value): static
    {
        return $this->set('honeypot_field_name', $fieldName)->set('hidden_honeypot_field', $value);
    }

    public function getLanguages(): ?string
    {
        return $this->get('blog_lang');
    }

    public function setLanguages(?string $languages): static
    {
        return $this->set('blog_lang', $languages);
    }

    public function getPermalink(): ?string
    {
        return $this->get('permalink');
    }

    public function setPermalink(?string $permalink): static
    {
        return $this->set('permalink', $permalink);
    }

    public function getRecheckReason(): ?string
    {
        return $this->get('recheck_reason');
    }

    public function setRecheckReason(?string $reason): static
    {
        return $this->set('recheck_reason', $reason);
    }

    public function getReferrer(): ?string
    {
        return $this->get('referrer');
    }

    public function setReferrer(?string $referrer): static
    {
        return $this->set('referrer', $referrer);
    }

    public function getType(): ?MessageType
    {
        return MessageType::tryFrom($this->get('comment_type') ?? '');
    }

    public function setType(?MessageType $type): static
    {
        return $this->set('comment_type', $type?->value);
    }

    public function getUserAgent(): ?string
    {
        return $this->get('user_agent');
    }

    public function setUserAgent(?string $ua): static
    {
        return $this->set('user_agent', $ua);
    }

    public function getUserIP(): ?string
    {
        return $this->get('user_ip');
    }

    public function setUserIP(string $ip): static
    {
        return $this->set('user_ip', $ip);
    }

    public function getUserRole(): ?string
    {
        return $this->get('user_role');
    }

    public function setUserRole(?string $role): static
    {
        return $this->set('user_role', $role);
    }

    public function addContext(string $context): static
    {
        $this->values['comment_context'][] = $context;

        return $this;
    }

    public function clearContext(): static
    {
        $this->values['comment_context'] = [];

        return $this;
    }

    /**
     * @return string[]
     */
    public function getContext(): array
    {
        return $this->values['comment_context'];
    }

    /**
     * @return array<string, string|string[]>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    private function get(string $key): ?string
    {
        return $this->values[$key] ?? null;
    }

    private function set(string $key, ?string $value): static
    {
        if (null === $value) {
            unset($this->values[$key]);
        } else {
            $this->values[$key] = $value;
        }

        return $this;
    }

    public static function fromRequest(Request $request): self
    {
        return (new self())
            ->setReferrer($request->headers->get('Referer'))
            ->setUserAgent($request->headers->get('User-Agent'))
            ->setUserIP($request->getClientIp() ?? '127.0.0.1')
        ;
    }

    public static function fromPSR7Request(ServerRequestInterface $request): self
    {
        $params = $request->getServerParams();

        return (new self())
            ->setReferrer($params['HTTP_REFERER'] ?? null)
            ->setUserAgent($params['HTTP_USER_AGENT'] ?? null)
            ->setUserIP($params['REMOTE_ADDR'] ?? '127.0.0.1')
        ;
    }
}
