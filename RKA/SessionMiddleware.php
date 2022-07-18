<?php
/**
 * Start a session.
 *
 * Copyright 2015 Rob Allen (rob@akrabat.com).
 * License: New-BSD
 */
namespace RKA;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class SessionMiddleware implements MiddlewareInterface
{
    protected $options = [
        'name' => 'RKA',
        'lifetime' => 7200,
        'path' => null,
        'domain' => null,
        'secure' => false,
        'httponly' => true,
        'cache_limiter' => 'nocache',
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
     * invoke middleware slim3
     *
     * @param ServerRequestInterface $request PSR7 request object
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface PSR7 response object
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->start();

        return $next($request, $response);
    }

    /**
     * process middleware slim4
     *
     * @param ServerRequestInterface  $request PSR7 request object
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface PSR7 response object
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->start();

        return $handler->handle($request);
    }

    public function start(): void
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
        session_cache_limiter($options['cache_limiter']);
        session_start();
    }
}
