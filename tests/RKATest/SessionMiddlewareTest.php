<?php
namespace RKATest;

use PHPUnit\Framework\TestCase;
use RKA\SessionMiddleware;
use Slim\Psr7\Environment;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UriFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Psr7\UploadedFile;

class SessionMiddlewareTest extends TestCase
{
    public function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testDefaults(): void
    {
        $session = new SessionMiddleware();

        $this->assertEquals(PHP_SESSION_NONE, session_status());
        $session->start();

        $expected = [
            'lifetime' => 7200,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => '',
        ];
        $this->assertEquals($expected, session_get_cookie_params());

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
        $this->assertEquals('RKA', session_name());
    }

    public function testOptions(): void
    {
        $session = new SessionMiddleware([
            'name' => 'Test',
            'lifetime' => '3600',
            'path' => '/test',
            'domain' => 'example.com',
            'secure' => true,
            'httponly' => false,
        ]);

        $this->assertEquals(PHP_SESSION_NONE, session_status());
        $session->start();

        $expected = [
            'lifetime' => 3600,
            'path' => '/test',
            'domain' => 'example.com',
            'secure' => true,
            'httponly' => false,
            'samesite' => '',
        ];
        $this->assertEquals($expected, session_get_cookie_params());

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
        $this->assertEquals('Test', session_name());
    }

    public function testStartingSessionTwiceCausesNoWarning(): void
    {
        $session = new SessionMiddleware([]);

        $this->assertEquals(PHP_SESSION_NONE, session_status());
        $session->start();
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());

        $session->start();
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
    }

    public function testCallStartsSession(): void
    {
        $session = new SessionMiddleware([]);

        $env = Environment::mock();
        $uri = (new UriFactory())->createUri('https://example.com:443/foo/bar?abc=123');
        $headers = Headers::createFromGlobals($env);
        $cookies = [];
        $serverParams = $env;
        $body = (new StreamFactory())->createStream();
        $uploadedFiles = UploadedFile::createFromGlobals($env);

        $request = new Request('GET', $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);
        $response = new Response();
        $next = function ($request, $response) {
            return $response;
        };

        $this->assertEquals(PHP_SESSION_NONE, session_status());
        @$session($request, $response, $next); // silence cookie warning
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
    }
}
