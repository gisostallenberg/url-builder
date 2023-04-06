<?php

namespace GisoStallenberg\UrlBuilder;

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

    public function __construct(string $url = null)
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
        return $this
            ->reset()
            ->setComponents((array) \parse_url($url));
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
     * Gives the url as string
     */
    public function getUrl(): string
    {
        return static::joinUrl($this->getComponents());
    }

    /**
     * Transforms this object into a string
     */
    public function __toString(): string
    {
        return $this->getUrl();
    }

    /**
     * Got the code for this method from http://nadeausoftware.com/articles/2008/05/php_tip_how_parse_and_build_urls#Downloads
     * OSI BSD license
     *
     * @param array<string, mixed> $parts
     */
    public static function joinUrl(array $parts, bool $encodeUrl = false): string
    {
        if (isset($parts['query']) && \is_array($parts['query'])) {
            $parts['query'] = \http_build_query($parts['query']);
        }

        if ($encodeUrl) {
            if (isset($parts['user'])) {
                $parts['user'] = \rawurlencode((string) $parts['user']);
            }
            if (isset($parts['pass'])) {
                $parts['pass'] = \rawurlencode((string) $parts['pass']);
            }
            if (isset($parts['host']) &&  !\preg_match('!^(\[[\da-f.:]+\]])|([\da-f.:]+)$!ui', (string) $parts['host'])) {
                $parts['host'] = \rawurlencode((string) $parts['host']);
            }
            if (!empty($parts['path'])) {
                $parts['path'] = \preg_replace('!%2F!ui', '/', \rawurlencode((string) $parts['path']));
            }
            if (isset($parts['query'])) {
                $parts['query'] = \rawurlencode((string) $parts['query']);
            }
            if (isset($parts['fragment'])) {
                $parts['fragment'] = \rawurlencode((string) $parts['fragment']);
            }
        }

        $url = '';
        if (!empty($parts['scheme'])) {
            $url .= $parts['scheme'].':';
        }
        if (isset($parts['host'])) {
            $url .= '//';
            if (isset($parts['user'])) {
                $url .= $parts['user'];
                if (isset($parts['pass'])) {
                    $url .= ':'.$parts['pass'];
                }
                $url .= '@';
            }
            if (\preg_match('!^[\da-f]*:[\da-f.:]+$!ui', (string) $parts['host'])) {
                $url .= '['.$parts['host'].']'; // IPv6
            } else {
                $url .= $parts['host']; // IPv4 or name
            }
            if (isset($parts['port'])) {
                $url .= ':'.$parts['port'];
            }
            if (!empty($parts['path']) && $parts['path'][0] != '/') {
                $url .= '/';
            }
        }
        if (!empty($parts['path'])) {
            $url .= $parts['path'];
        }
        if (isset($parts['query'])) {
            $url .= '?'.$parts['query'];
        }
        if (isset($parts['fragment'])) {
            $url .= '#'.$parts['fragment'];
        }

        return $url;
    }
}
