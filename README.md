# RKA Slim Session Middleware

Middleware for [Slim Framework][1] that starts a session. Also provides a useful `Session` class.

## Installation

    composer require "akrabat/rka-slim-session-middleware"

## Usage

Add middleware as usual:

    $app->add(new \RKA\SessionMiddleware(['name' => 'MySessionName']));


### RKA\Session

You can use `\RKA\Session` to access session variables. The main thing that this gives you is defaults and an OO interface:

    $app->get('/', function ($request, $response) {
        $session = new \RKA\Session();

        // Get session variable:
        $foo = $session->get('foo', 'some-default');
        $bar = $session->bar;

        // Set session variable:
        $session->foo = 'this';
        $session->set('bar', 'that');

        return $response;
    });


if you need to destroy the session, you can do:

    \RKA\Session::destroy();


[1]: http://www.slimframework.com/
