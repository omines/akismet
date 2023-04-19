<?php

/*
 * Akismet
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Omines\Akismet\Akismet;
use Omines\Akismet\AkismetMessage;
use Omines\Akismet\MessageType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AkismetTest extends TestCase
{
    private static string $apiKey;

    private Akismet $akismet;

    public function testPropertiesAndOverrides(): void
    {
        // During testing the default testing value is true
        $this->assertTrue($this->akismet->isTesting());
        $this->akismet->setTesting(false);
        $this->assertFalse($this->akismet->isTesting());

        $this->akismet->setApiKey('foo.bar');
        $this->assertSame('foo.bar', $this->akismet->getApiKey());

        $this->akismet->setInstance('https://foo.bar');
        $this->assertSame('https://foo.bar', $this->akismet->getInstance());

        $this->assertInstanceOf(HttpClientInterface::class, $this->akismet->getClient());
    }

    public function testMessageAccessors(): void
    {
        $request = Request::create('https://www.example.org/post-comment', 'POST', server: [
            'REMOTE_ADDR' => '12.34.56.78',
            'HTTP_REFERER' => 'https://www.google.com',
            'HTTP_USER_AGENT' => 'Custom Browser 684',
        ]);

        $message = AkismetMessage::fromRequest($request)
            ->setAuthor('foo')
            ->setAuthorEmail('foo@bar.org')
            ->setContent('foo bar')
            ->setType(MessageType::BLOG_POST)
            ->setUserRole('guest')
        ;

        $this->assertSame('foo', $message->getAuthor());
        $this->assertSame('foo@bar.org', $message->getAuthorEmail());
        $this->assertSame('foo bar', $message->getContent());
        $this->assertSame('https://www.google.com', $message->getReferrer());
        $this->assertSame(MessageType::BLOG_POST, $message->getType());
        $this->assertSame('Custom Browser 684', $message->getUserAgent());
        $this->assertSame('guest', $message->getUserRole());
        $this->assertSame('12.34.56.78', $message->getUserIP());

        $message->setReferrer(null);
        $this->assertNull($message->getReferrer());
    }

    public function testPSR7Integration(): void
    {
        $request = (new Psr17Factory())->createServerRequest('GET', 'https://example.org/post-comment', [
            'REMOTE_ADDR' => '12.34.56.78',
            'HTTP_REFERER' => 'https://www.google.com',
            'HTTP_USER_AGENT' => 'Custom Browser 684',
        ]);

        $message = AkismetMessage::fromPSR7Request($request);

        $this->assertSame('https://www.google.com', $message->getReferrer());
        $this->assertSame('Custom Browser 684', $message->getUserAgent());
        $this->assertSame('12.34.56.78', $message->getUserIP());
        $this->assertNull($message->getUserRole());
    }

    public function testMissingApiKeyFails(): void
    {
        $akismet = new Akismet(HttpClient::create());
        $this->expectExceptionMessage('API key must be set');
        $akismet->verifyKey();
    }

    public function testKeyVerification(): void
    {
        $response = $this->akismet->verifyKey();
        $this->assertTrue($response->isValid());
    }

    public function testHamCommentCheck(): void
    {
        $message = (new AkismetMessage())
            ->setUserIP('1.2.3.4')
            ->setUserRole('administrator')
            ->setContent('Thank you for this interesting contribution, I will remember this')
            ->setType(MessageType::CONTACT_FORM)
        ;

        $response = $this->akismet->check($message);

        $this->assertSame($message, $response->getMessage());
        $this->assertSame($this->akismet, $response->getAkismet());

        $this->assertFalse($response->isSpam());
        $this->assertFalse($response->shouldDiscard());
    }

    public function testSpamCommentCheck(): void
    {
        $response = $this->akismet->check((new AkismetMessage())
            ->setUserIP('1.2.3.4')
            ->setAuthor('akismet-guaranteed-spam')
            ->setAuthorEmail('akismet-guaranteed-spam@example.com')
            ->setContent('You have won $700.000 in our raffle, visit https://spam.ru for information!!!')
            ->setType(MessageType::REPLY)
        );

        $this->assertTrue($response->isSpam());
        $this->assertFalse($response->shouldDiscard());
    }

    protected function setUp(): void
    {
        $this->akismet = new Akismet(HttpClient::create(), self::$apiKey, 'https://www.example.org/', true);
    }

    public static function setUpBeforeClass(): void
    {
        (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

        if (!array_key_exists('AKISMET_KEY', $_ENV)) {
            throw new \LogicException('Make sure the AKISMET_KEY env variable is set either through a .env.local file or actual environment variables');
        }
        self::$apiKey = $_ENV['AKISMET_KEY'];
    }
}
