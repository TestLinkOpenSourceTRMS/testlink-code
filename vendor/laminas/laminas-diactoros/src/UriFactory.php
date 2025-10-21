<?php

declare(strict_types=1);

namespace Laminas\Diactoros;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

use function array_change_key_case;
use function array_key_exists;
use function assert;
use function count;
use function explode;
use function gettype;
use function implode;
use function is_bool;
use function is_scalar;
use function is_string;
use function ltrim;
use function preg_match;
use function preg_replace;
use function sprintf;
use function str_contains;
use function strlen;
use function strrpos;
use function strtolower;
use function substr;

use const CASE_LOWER;

class UriFactory implements UriFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }

    /**
     * Create a Uri instance based on the headers and $_SERVER data.
     *
     * @param array<non-empty-string, list<string>|int|float|string> $server SAPI parameters
     * @param array<string, string|list<string>> $headers
     */
    public static function createFromSapi(array $server, array $headers): Uri
    {
        $uri = new Uri('');

        $isHttps = false;
        if (array_key_exists('HTTPS', $server)) {
            $isHttps = self::marshalHttpsValue($server['HTTPS']);
        } elseif (array_key_exists('https', $server)) {
            $isHttps = self::marshalHttpsValue($server['https']);
        }
        $uri = $uri->withScheme($isHttps ? 'https' : 'http');

        [$host, $port] = self::marshalHostAndPort($server, $headers);
        if (! empty($host)) {
            $uri = $uri->withHost($host);
            if (! empty($port)) {
                $uri = $uri->withPort($port);
            }
        }

        $path = self::marshalRequestPath($server);

        // Strip query string
        $path = explode('?', $path, 2)[0];

        $query = '';
        if (isset($server['QUERY_STRING']) && is_scalar($server['QUERY_STRING'])) {
            $query = ltrim((string) $server['QUERY_STRING'], '?');
        }

        $fragment = '';
        if (str_contains($path, '#')) {
            $parts = explode('#', $path, 2);
            assert(count($parts) >= 2);
            [$path, $fragment] = $parts;
        }

        return $uri
            ->withPath($path)
            ->withFragment($fragment)
            ->withQuery($query);
    }

    /**
     * Retrieve a header value from an array of headers using a case-insensitive lookup.
     *
     * @template T
     * @param array<string, string|list<string>> $headers Key/value header pairs
     * @param T $default Default value to return if header not found
     * @return string|T
     */
    private static function getHeaderFromArray(string $name, array $headers, $default = null)
    {
        $header  = strtolower($name);
        $headers = array_change_key_case($headers, CASE_LOWER);
        if (! array_key_exists($header, $headers)) {
            return $default;
        }

        if (is_string($headers[$header])) {
            return $headers[$header];
        }

        return implode(', ', $headers[$header]);
    }

    /**
     * Marshal the host and port from the PHP environment.
     *
     * @param array<string, string|list<string>> $headers
     * @return array{string, int|null} Array of two items, host and port,
     *     in that order (can be passed to a list() operation).
     */
    private static function marshalHostAndPort(array $server, array $headers): array
    {
        /** @var array{string, null} $defaults */
        static $defaults = ['', null];

        $host = self::getHeaderFromArray('host', $headers, false);
        if ($host !== false) {
            // Ignore obviously malformed host headers:
            // - Whitespace is invalid within a hostname and break the URI representation within HTTP.
            //   non-printable characters other than SPACE and TAB are already rejected by HeaderSecurity.
            // - A comma indicates that multiple host headers have been sent which is not legal
            //   and might be used in an attack where a load balancer sees a different host header
            //   than Diactoros.
            if (! preg_match('/[\\t ,]/', $host)) {
                return self::marshalHostAndPortFromHeader($host);
            }
        }

        if (! isset($server['SERVER_NAME'])) {
            return $defaults;
        }

        $host = (string) $server['SERVER_NAME'];
        $port = isset($server['SERVER_PORT']) ? (int) $server['SERVER_PORT'] : null;

        if (
            ! isset($server['SERVER_ADDR'])
            || ! preg_match('/^\[[0-9a-fA-F\:]+\]$/', $host)
        ) {
            return [$host, $port];
        }

        // Misinterpreted IPv6-Address
        // Reported for Safari on Windows
        return self::marshalIpv6HostAndPort($server, $port);
    }

    /**
     * @return array{string, int|null} Array of two items, host and port,
     *     in that order (can be passed to a list() operation).
     */
    private static function marshalIpv6HostAndPort(array $server, ?int $port): array
    {
        $host             = '[' . (string) $server['SERVER_ADDR'] . ']';
        $port             = $port ?: 80;
        $portSeparatorPos = strrpos($host, ':');

        if (false === $portSeparatorPos) {
            return [$host, $port];
        }

        if ($port . ']' === substr($host, $portSeparatorPos + 1)) {
            // The last digit of the IPv6-Address has been taken as port
            // Unset the port so the default port can be used
            $port = null;
        }
        return [$host, $port];
    }

    /**
     * Detect the path for the request
     *
     * Looks at a variety of criteria in order to attempt to autodetect the base
     * request path, including:
     *
     * - IIS7 UrlRewrite environment
     * - REQUEST_URI
     * - ORIG_PATH_INFO
     */
    private static function marshalRequestPath(array $server): string
    {
        // IIS7 with URL Rewrite: make sure we get the unencoded url
        // (double slash problem).
        /** @var string|array<string>|null $iisUrlRewritten */
        $iisUrlRewritten = $server['IIS_WasUrlRewritten'] ?? null;
        /** @var string|array<string> $unencodedUrl */
        $unencodedUrl = $server['UNENCODED_URL'] ?? '';
        if ('1' === $iisUrlRewritten && is_string($unencodedUrl) && '' !== $unencodedUrl) {
            return $unencodedUrl;
        }

        /** @var string|array<string>|null $requestUri */
        $requestUri = $server['REQUEST_URI'] ?? null;

        if (is_string($requestUri)) {
            return preg_replace('#^[^/:]+://[^/]+#', '', $requestUri);
        }

        $origPathInfo = $server['ORIG_PATH_INFO'] ?? '';
        if (! is_string($origPathInfo) || '' === $origPathInfo) {
            return '/';
        }

        return $origPathInfo;
    }

    private static function marshalHttpsValue(mixed $https): bool
    {
        if (is_bool($https)) {
            return $https;
        }

        if (! is_string($https)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'SAPI HTTPS value MUST be a string or boolean; received %s',
                gettype($https)
            ));
        }

        return 'on' === strtolower($https);
    }

    /**
     * @internal
     *
     * @return array{string, int|null} Array of two items, host and port, in that order (can be
     *     passed to a list() operation).
     * @psalm-mutation-free
     */
    public static function marshalHostAndPortFromHeader(string $host): array
    {
        $port = null;

        // works for regname, IPv4 & IPv6
        if (preg_match('|\:(\d+)$|', $host, $matches)) {
            $host = substr($host, 0, -1 * (strlen($matches[1]) + 1));
            $port = (int) $matches[1];
        }

        return [$host, $port];
    }
}
