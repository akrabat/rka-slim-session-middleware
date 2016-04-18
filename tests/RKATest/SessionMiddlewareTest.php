<?php
namespace RKATest;

use RKA\SessionMiddleware;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class SessionMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function testDefaults()
    {
        $session = new SessionMiddleware();

        $this->assertEquals(PHP_SESSION_NONE, session_status());
        @$session->start(); // silence cookie warning

        $expected = [
            'lifetime' => 7200,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
        ];
        $this->assertEquals($expected, session_get_cookie_params());

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
        $this->assertEquals('RKA', session_name());
    }

    public function testOptions()
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
        @$session->start(); // silence cookie warning

        $expected = [
            'lifetime' => 3600,
            'path' => '/test',
            'domain' => 'example.com',
            'secure' => true,
            'httponly' => false,
        ];
        $this->assertEquals($expected, session_get_cookie_params());

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
        $this->assertEquals('Test', session_name());
    }

    public function testStartingSessionTwiceCausesNoWarning()
    {
        $session = new SessionMiddleware([]);

        $this->assertEquals(PHP_SESSION_NONE, session_status());
        @$session->start(); // silence cookie warning
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());

        $session->start();
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
    }

    public function testCallStartsSession()
    {
        $session = new SessionMiddleware([]);

        $request = Request::createFromEnvironment(Environment::mock());
        $response = new Response();
        $next = function ($request, $response, $next) {
            return $response;
        };

        $this->assertEquals(PHP_SESSION_NONE, session_status());
        @$session($request, $response, $next); // silence cookie warning
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
    }
}
