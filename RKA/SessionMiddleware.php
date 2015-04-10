<?php
/**
 * Start a session.
 *
 * Copyright 2015 Rob Allen (rob@akrabat.com).
 * License: New-BSD
 */
namespace RKA;

use Slim\Middleware;

final class SessionMiddleware extends Middleware
{
    protected $options;

    public function __construct($options = [])
    {
        $defaults = [
          'name' => 'RKA',
          'lifetime' => 7200,
          'path' => '/',
          'domain' => null,
          'secure' => false,
          'httponly' => false,
        ];
        $this->options = array_merge($defaults, $options);
        if (is_string($lifetime = $this->options['lifetime'])) {
            $this->options['lifetime'] = strtotime($lifetime) - time();
        }
    }

    public function call()
    {
        $this->start();
        $this->next->call();
    }

    public function start()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            return;
        }

        $options = $this->options;
        session_set_cookie_params(
            $options['lifetime'],
            $options['path'],
            $options['domain'],
            $options['secure'],
            $options['httponly']
        );
        session_name($options['name']);
        session_cache_limiter(false); //http://docs.slimframework.com/#Sessions
        session_start();
    }
}
