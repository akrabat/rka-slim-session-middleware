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
    protected $options = [
        'name' => 'RKA',
        'lifetime' => 7200,
        'path' => null,
        'domain' => null,
        'secure' => false,
        'httponly' => true,
    ];

    public function __construct($options = [])
    {
        $keys = array_keys($this->options);
        foreach ($keys as $key) {
            if (array_key_exists($key, $options)) {
                $this->options[$key] = $options[$key];
            }
        }
    }

    /**
     * Invoke middleware for Slim 3
     *
     * @param  RequestInterface  $request  PSR7 request object
     * @param  ResponseInterface $response PSR7 response object
     * @param  callable          $next     Next middleware callable
     *
     * @return ResponseInterface PSR7 response object
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->start();
        return $next($request, $response);
    }


    /**
     * Invoke middleware for Slim 2
    */
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
        $current = session_get_cookie_params();
        
        $lifetime = (int)($options['lifetime'] ?: $current['lifetime']);
        $path     = $options['path'] ?: $current['path'];
        $domain   = $options['domain'] ?: $current['domain'];
        $secure   = (bool)$options['secure'];
        $httponly = (bool)$options['httponly'];

        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
        session_name($options['name']);
        session_cache_limiter(false); //http://docs.slimframework.com/#Sessions
        session_start();
    }
}
