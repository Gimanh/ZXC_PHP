<?php

namespace ZXC\Native\HTTP;

use ZXC\Patterns\Singleton;

class Request
{
    use Singleton;

    /**
     * https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
     * @var array
     */
    public $headers = [
        // 1xx Informational responses
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // 2xx Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // 3xx Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        // 4xx Client errors
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // 5xx Server errors
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];
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
        if (isset($this->server['REQUEST_URI']) && $this->server['REQUEST_URI'] === '/') {
            $path['path'] = $this->server['REQUEST_URI'];
        } else {
            $path = parse_url($this->server['REQUEST_URI']);
        }
        $this->path = $path['path'];
        $this->post = $_POST;
        $this->get = $_GET;
        $this->baseRoute = dirname($server['SCRIPT_NAME']);
        $this->host = isset($server['SERVER_NAME']) ? $server['SERVER_NAME'] : null;
        $this->port = $this->server['SERVER_PORT'];
        $this->scheme = (!empty($server['HTTPS']) && $server['HTTPS'] !== 'off' || $server['SERVER_PORT'] == 443) ? "https://" : "http://";
        $this->protocol = $this->server['SERVER_PROTOCOL'];
        $this->normalize();
    }

    public function normalize()
    {
        if ($this->path !== '/' && $this->baseRoute !== '/') {
            $lastSlash = substr($this->path, -1);
            if ($lastSlash === '/') {
                $this->path = rtrim($this->path, '/');
            }
            $position = strpos($this->path, $this->baseRoute);
            if ($position === 0) {
                $this->path = str_replace($this->baseRoute, '', $this->path);
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