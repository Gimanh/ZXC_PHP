<?php

namespace ZXC\Native\HTTP;

use ZXC\Patterns\Singleton;
use ZXC\Traits\GetSet;

class Request
{
    use Singleton, GetSet;

    private $get;
    private $host;
    private $path;
    private $post;
    private $port;
    private $server;
    private $method;
    private $scheme;
    private $protocol;
    private $baseRoute;

    public function initialize(array $server = [])
    {
        if (!$server) {
            throw new \InvalidArgumentException('SERVER is not defined');
        }
        $this->server = $server;
        $this->method = $this->server['REQUEST_METHOD'];
        $cleanURIPath = preg_replace('#/+#', '/', $this->server['REQUEST_URI']);
        if ($cleanURIPath === '/') {
            $this->path = $cleanURIPath;
        } else {
            $path = parse_url($cleanURIPath);
            $this->path = $path['path'];
        }
        $this->post = $_POST;
        $this->get = $_GET;
        $this->baseRoute = dirname($server['SCRIPT_NAME']);
        $this->host = isset($server['SERVER_NAME']) ? $server['SERVER_NAME'] : null;
        $this->port = $this->server['SERVER_PORT'];
        $this->scheme = (!empty($server['HTTPS']) && $server['HTTPS'] !== 'off' || $server['SERVER_PORT'] == 443) ? "https://" : "http://";
        $this->protocol = $this->server['SERVER_PROTOCOL'];
        $this->normalize();
        return true;
    }

    public function normalize()
    {
        if ($this->path !== '/' && $this->baseRoute !== '/') {
            $lastSlash = substr($this->path, -1);
            if ($lastSlash === '/') {
                $this->path = rtrim($this->path, '/');
            }
            if ($this->path === $this->baseRoute) {
                $this->path = '/';
            } elseif ($this->path !== $this->baseRoute) {
                $position = strpos($this->path, $this->baseRoute);
                if ($position === 0) {
                    $this->path = str_replace($this->baseRoute, '', $this->path);
                }
            }
        }
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getGet()
    {
        return $this->get;
    }

    public function getBaseRoute()
    {
        return $this->baseRoute;
    }

    public function getInputData($name)
    {
        if (isset($this->post[$name])) {
            return $this->post[$name];
        } elseif (isset($this->get[$name])) {
            return $this->get[$name];
        } else {
            return false;
        }
    }

    public function getURL()
    {
        return $this->scheme . '://' . $this->host;
    }
}