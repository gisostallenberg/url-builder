<?php

namespace GisoStallenberg\UrlBuilder;

use Uri\Rfc3986\Uri;

final class UrlBuilder implements \Stringable
{
    private ?string $scheme = null;

    private ?string $host = null;

    private ?string $port = null;

    private ?string $user = null;

    private ?string $pass = null;

    private ?string $path = null;

    /**
     * @var array<string, mixed>
     */
    private array $query = [];

    private ?string $fragment = null;

    public function __construct(?string $url = null)
    {
        if (isset($url)) {
            $this->valuesFromUrl($url);
        }
    }

    /**
     * Resets all internal properties
     */
    private function reset(): self
    {
        $this->scheme = null;
        $this->host = null;
        $this->port = null;
        $this->user = null;
        $this->pass = null;
        $this->path = null;
        $this->query = [];
        $this->fragment = null;

        return $this;
    }

    /**
     * Sets the individual properties using the given url
     */
    public function valuesFromUrl(string $url): self
    {
        $this->reset();

        // ponytail: the query is parsed separately below, so strip it before
        // handing the URL to Uri::parse. Uri is RFC 3986-strict and rejects the
        // whole URL outright when the query holds unencoded chars (e.g. a[]=1),
        // which PHP apps routinely produce.
        $uri = Uri::parse($this->withoutQuery($url));
        if ($uri === null) {
            throw new \InvalidArgumentException(\sprintf('Malformed URL: %s', $url));
        }

        $this->scheme = $uri->getScheme();
        $this->host = $uri->getHost();
        $this->port = $uri->getPort() === null ? null : (string) $uri->getPort();
        $this->user = $uri->getUsername();
        $this->pass = $uri->getPassword();
        $this->path = $uri->getPath() === '' ? null : $uri->getPath();
        $this->fragment = $uri->getFragment();

        // ponytail: query kept as a PHP array via parse_str/http_build_query, not
        // via Uri's query getter, which is RFC 3986-strict and silently drops the
        // a[]=1&a[]=2 style PHP apps rely on. Slice it out ourselves instead.
        $this->setQuery($this->extractQuery($url));

        return $this;
    }

    private function extractQuery(string $url): string
    {
        $start = \strpos($url, '?');
        if ($start === false) {
            return '';
        }

        $query = \substr($url, $start + 1);
        $end = \strpos($query, '#');

        return $end === false ? $query : \substr($query, 0, $end);
    }

    private function withoutQuery(string $url): string
    {
        $start = \strpos($url, '?');
        if ($start === false) {
            return $url;
        }

        $fragment = \strpos($url, '#', $start);

        return \substr($url, 0, $start) . ($fragment === false ? '' : \substr($url, $fragment));
    }

    /**
     * Creates a new UrlBuilder using info in $_SERVER
     */
    public static function createFromGlobals(): self
    {
        $url = 'http';
        $url .= isset($_SERVER['HTTPS']) ? 's' : ''; // do not check for ssl termination, this should not be done in UrlBuilder, use setScheme to correct the scheme in case of SSL termination
        $url .= '://';
        $url .= $_SERVER['HTTP_HOST'] ?? '';
        $url .= $_SERVER['REQUEST_URI'] ?? '';

        return static::createFromUrl($url);
    }

    /**
     * Creates a new UrlBuilder using $url
     */
    public static function createFromUrl(string $url): self
    {
        return new UrlBuilder($url);
    }

    /**
     * Creates a new UrlBuilder using $components
     *
     * @param array<int|string, bool|int|string> $components
     */
    public static function createFromComponents(array $components): self
    {
        return (new UrlBuilder())->setComponents($components);
    }

    /**
     * Fills the values from an array of components
     *
     * @param array<int|string, bool|int|string> $components
     */
    public function setComponents(array $components): self
    {
        foreach ($components as $key => $value) {
            $setter = \sprintf('set%s', \ucfirst((string) $key));
            $this->{$setter}($value);
        }

        return $this;
    }

    public function setScheme(?string $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function setHost(?string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function setPort(?string $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function setUser(?string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setPass(?string $pass): self
    {
        $this->pass = $pass;

        return $this;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param array<string, mixed>|string|null $query
     */
    public function setQuery(array|string|null $query): self
    {
        if (\is_string($query)) {
            \parse_str($query, $this->query);
        } else {
            $this->query = $query ?? [];
        }

        return $this;
    }

    public function setFragment(?string $fragment): self
    {
        $this->fragment = $fragment;

        return $this;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getPort(): ?string
    {
        return $this->port;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @return array<string, mixed>
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * Gives an array with all the components of this url, leaves out empty values
     *
     * @return array<string, mixed>
     */
    public function getComponents(): array
    {
        $components = [];
        if (!empty($this->scheme)) {
            $components['scheme'] = $this->scheme;
        }
        if (!empty($this->host)) {
            $components['host'] = $this->host;
        }
        if (!empty($this->port)) {
            $components['port'] = $this->port;
        }
        if (!empty($this->user)) {
            $components['user'] = $this->user;
        }
        if (!empty($this->pass)) {
            $components['pass'] = $this->pass;
        }
        if (!empty($this->path)) {
            $components['path'] = $this->path;
        }
        if (!empty($this->query)) {
            $components['query'] = $this->query;
        }
        if (!empty($this->fragment)) {
            $components['fragment'] = $this->fragment;
        }

        return $components;
    }

    public function appendToQuery(string $key, mixed $value): self
    {
        if (isset($this->query[$key]) && \is_array($this->query[$key])) {
            $this->query[$key][] = $value;
        } else {
            $this->query[$key] = $value;
        }

        return $this;
    }

    /**
     * Unsets a value in the query, does not check or complain about presence
     * If $value is given only unsets if value is the same
     */
    public function unsetInQuery(string $key, mixed $value = false): self
    {
        if ($value === false || (isset($this->query[$key]) && $this->query[$key] == $value)) {
            unset($this->query[$key]);
        }

        return $this;
    }

    /**
     * Gives the url as string, built with the native Uri extension
     */
    public function getUrl(): string
    {
        $uri = new Uri('');

        if (!empty($this->scheme)) {
            $uri = $uri->withScheme($this->scheme);
        }

        $hasHost = !empty($this->host);
        if ($hasHost) {
            $uri = $uri->withHost($this->host);

            // Uri rejects userinfo/port without a host, mirroring the old builder
            // which only emitted them inside the authority.
            if (!empty($this->user)) {
                $userInfo = $this->user;
                if ($this->pass !== null) {
                    $userInfo .= ':' . $this->pass;
                }
                $uri = $uri->withUserInfo($userInfo);
            }
            if (!empty($this->port)) {
                $uri = $uri->withPort((int) $this->port);
            }
        }

        if (!empty($this->path)) {
            $path = $this->path;
            if ($hasHost && $path[0] !== '/') {
                $path = '/' . $path;
            }
            $uri = $uri->withPath($path);
        }

        if ($this->query !== []) {
            $uri = $uri->withQuery(\http_build_query($this->query));
        }

        if (!empty($this->fragment)) {
            $uri = $uri->withFragment($this->fragment);
        }

        return $uri->toString();
    }

    /**
     * Builds a URL from a parse_url()-style components array.
     *
     * ponytail: kept for backwards compatibility, delegating to the native
     * build. The $encodeUrl flag is now a no-op — Uri always percent-encodes
     * per RFC 3986, so there is no separate raw/encoded output to toggle. Drop
     * the parameter on the next major if nothing passes it.
     *
     * @param array<string, mixed> $parts
     */
    public static function joinUrl(array $parts, bool $encodeUrl = false): string
    {
        return self::createFromComponents($parts)->getUrl();
    }

    /**
     * Transforms this object into a string
     */
    public function __toString(): string
    {
        return $this->getUrl();
    }
}
