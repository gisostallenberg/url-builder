<?php

namespace GisoStallenberg\UrlBuilder;

class UrlBuilder
{
    /**
     * The scheme.
     *
     * @var string
     */
    private $scheme;

    /**
     * The scheme.
     *
     * @var string
     */
    private $host;

    /**
     * The scheme.
     *
     * @var string
     */
    private $port;

    /**
     * The scheme.
     *
     * @var string
     */
    private $user;

    /**
     * The scheme.
     *
     * @var string
     */
    private $pass;

    /**
     * The scheme.
     *
     * @var string
     */
    private $path;

    /**
     * The scheme.
     *
     * @var string
     */
    private $query = array();

    /**
     * The scheme.
     *
     * @var string
     */
    private $fragment;

    /**
     * Create a new instance of an UrlBuilder.
     *
     * @param type $url
     */
    public function __construct($url = null)
    {
        if (isset($url)) {
            $this->valuesFromUrl($url);
        }
    }

    /**
     * reset.
     *
     * Resets all internal properties
     */
    private function reset()
    {
        $this->scheme = null;
        $this->host = null;
        $this->port = null;
        $this->user = null;
        $this->pass = null;
        $this->path = null;
        $this->query = array();
        $this->fragment = null;
    }

    /**
     * valuesFromUrl.
     *
     * Sets the individual properties using the given url
     *
     * @param string $url
     *
     * @return UrlBuilder
     */
    public function valuesFromUrl($url)
    {
        $this->reset();
        $this->setComponents((array) parse_url($url));

        return $this;
    }

    /**
     * createFromGlobals.
     *
     * Creates a new UrlBuilder using info in $_SERVER
     *
     * @return UrlBuilder
     */
    public static function createFromGlobals()
    {
        $url = 'http';
        $url .= isset($_SERVER['HTTPS']) ? 's' : ''; // do not check for ssl termination, this should not be done in UrlBuilder, use setScheme to correct the scheme in case of SSL termination
        $url .= '://';
        $url .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $url .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        return static::createFromUrl($url);
    }

    /**
     * createFromUrl.
     *
     * Creates a new UrlBuilder using $url
     *
     * @param string $url
     *
     * @return UrlBuilder
     */
    public static function createFromUrl($url)
    {
        return new UrlBuilder($url);
    }

    /**
     * createFromComponents.
     *
     * Creates a new UrlBuilder using $components
     *
     * @param array $components
     *
     * @return UrlBuilder
     */
    public static function createFromComponents(array $components)
    {
        $urlbuilder = new UrlBuilder();
        $urlbuilder->setComponents($components);

        return $urlbuilder;
    }

    /**
     * setComponents.
     *
     * Fills the values from an array of components
     *
     * @param array $components
     *
     * @return UrlBuilder
     */
    public function setComponents(array $components)
    {
        foreach ($components as $key => $value) {
            if (!empty($value)) {
                $setter = 'set'.ucfirst($key);
                $this->$setter($value);
            }
        }

        return $this;
    }

    /**
     * setScheme.
     *
     * Sets the scheme value
     *
     * @param string $scheme
     *
     * @return UrlBuilder
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * setHost.
     *
     * Sets the host value
     *
     * @param string $host
     *
     * @return UrlBuilder
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * setPort.
     *
     * Sets the port value
     *
     * @param string $port
     *
     * @return UrlBuilder
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * setUser.
     *
     * Sets the user value
     *
     * @param string $user
     *
     * @return UrlBuilder
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * setPass.
     *
     * Sets the pass value
     *
     * @param string $pass
     *
     * @return UrlBuilder
     */
    public function setPass($pass)
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * setPath.
     *
     * Sets the path value
     *
     * @param string $path
     *
     * @return UrlBuilder
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * setQuery.
     *
     * Sets the query value
     *
     * @param array|string $query
     *
     * @return UrlBuilder
     */
    public function setQuery($query)
    {
        if (is_string($query)) {
            parse_str($query, $this->query);
        } elseif (is_array($query)) {
            $this->query = $query;
        }

        return $this;
    }

    /**
     * setFragment.
     *
     * Sets the fragment value
     *
     * @param string $fragment
     *
     * @return UrlBuilder
     */
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;

        return $this;
    }

    /**
     * getScheme.
     *
     * Returns the scheme value
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * getHost.
     *
     * Returns the host value
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * getPort.
     *
     * Returns the port value
     *
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * getUsre.
     *
     * Returns the user value
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * getPass.
     *
     * Returns the pass value
     *
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * getPath.
     *
     * Returns the path value
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * getQuery.
     *
     * Returns the query value
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * getFragment.
     *
     * Returns the fragment value
     *
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * getComponents.
     *
     * Gives an array with all the components of this url, leaves out empty values
     *
     * return array
     */
    public function getComponents()
    {
        $components = array();
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

    /**
     * appendToQuery.
     *
     * Appends a value to the query
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return UrlBuilder
     */
    public function appendToQuery($key, $value)
    {
        if (isset($this->query[$key]) && is_array($this->query[$key])) {
            $this->query[$key][] = $value;
        } else {
            $this->query[$key] = $value;
        }

        return $this;
    }

    /**
     * unsetInQuery.
     *
     * Unsets a value in the query, does not check or complain about presence
     * If $value is given only unsets if value is the same
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return UrlBuilder
     */
    public function unsetInQuery($key, $value = false)
    {
        if ($value === false || (isset($this->query[$key]) && $this->query[$key] == $value)) {
            unset($this->query[$key]);
        }

        return $this;
    }

    /**
     * getUrl.
     *
     * Gives the url
     *
     * @return string
     */
    public function getUrl()
    {
        return static::joinUrl($this->getComponents());
    }

    /**
     * __toString.
     *
     * Transforms this object into a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getUrl();
    }

    /**
     * joinUrl.
     *
     * Got the code for this method from http://nadeausoftware.com/articles/2008/05/php_tip_how_parse_and_build_urls#Downloads
     * OSI BSD license
     *
     * @param array $parts
     * @param bool  $encodeUrl
     *
     * @return string
     */
    public static function joinUrl(array $parts, $encodeUrl = false)
    {
        if (isset($parts['query']) && is_array($parts['query'])) {
            $parts['query'] = http_build_query($parts['query']);
        }

        if ($encodeUrl) {
            if (isset($parts['user'])) {
                $parts['user'] = rawurlencode($parts['user']);
            }
            if (isset($parts['pass'])) {
                $parts['pass'] = rawurlencode($parts['pass']);
            }
            if (isset($parts['host']) &&  !preg_match('!^(\[[\da-f.:]+\]])|([\da-f.:]+)$!ui', $parts['host'])) {
                $parts['host'] = rawurlencode($parts['host']);
            }
            if (!empty($parts['path'])) {
                $parts['path'] = preg_replace('!%2F!ui', '/', rawurlencode($parts['path']));
            }
            if (isset($parts['query'])) {
                $parts['query'] = rawurlencode($parts['query']);
            }
            if (isset($parts['fragment'])) {
                $parts['fragment'] = rawurlencode($parts['fragment']);
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
            if (preg_match('!^[\da-f]*:[\da-f.:]+$!ui', $parts['host'])) {
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
