# RKA Slim Session Middleware

Middleware for [Slim Framework][1] that starts a session. Also provides a useful `Session` class.

## Installation

```
composer require "akrabat/rka-slim-session-middleware"
```

## Usage

Add middleware as usual:

```php
$app->add(new \RKA\SessionMiddleware(['name' => 'MySessionName']));
```

### RKA\Session

You can use `\RKA\Session` to access session variables. The main thing that this gives you is defaults and an OO interface:

```php
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
```

If you need to clear or destroy the session, you can do:

```php
$session = new \RKA\Session();

// Delete a session variable
$session->delete('foo');

// Clear all session variables
$session->clearAll();

// Clear and destroy the session
$session->destroy();

// Generate a new session id
$session->regenerate();
```

[1]: http://www.slimframework.com/
