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

final class SessionMiddleware
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

        // Allow only cookies
        ini_set('session.use_only_cookies', 1);

        // Set lifetime
        ini_set('session.gc_maxlifetime', $this->options['lifetime']);
    }

    /**
     * Invoke middleware
     *
     * @param  RequestInterface  $request  PSR7 request object
     * @param  ResponseInterface $response PSR7 response object
     * @param  callable          $next     Next middleware callable
     *
     * @return ResponseInterface PSR7 response object
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->start($request);
        return $next($request, $response);
    }

    public function start(RequestInterface $request)
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

        // Initial session cookie setup
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
        session_name($options['name']);
        session_cache_limiter($options['cache_limiter']);
        session_start();

        $sessionId = session_id();

        // Manually expire session
        if (isset($_SESSION['_idle']) && (time() - $_SESSION['_idle'] > $lifetime)) {
            session_unset();
            session_destroy();
        }

        $_SESSION['_idle'] = time();

        // Manually extend session cookie expiry on each request (http://php.net/manual/en/function.session-set-cookie-params.php#100657)
        if ($request->getCookieParam($options['name']) === $sessionId) {
            setcookie($options['name'], $sessionId, time() + $lifetime, $path, $domain, $secure, $httponly);
        }
    }
}
