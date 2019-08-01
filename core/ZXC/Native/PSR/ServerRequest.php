<?php

namespace ZXC\Native\PSR;


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use ZXC\Native\Helper;

class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var array
     */
    protected $serverParams = [];
    /**
     * @var array
     */
    protected $cookieParams = [];
    /**
     * @var array
     */
    protected $queryParams = [];
    /**
     * @var UploadedFileInterface[]
     */
    protected $uploadedFiles = [];
    /**
     * @var null|array|object
     */
    protected $parsedBody;
    /**
     * @var array
     */
    protected $attributes;

    /**
     * ServerRequest constructor.
     * @param array $server
     * @param array $cookieParams
     * @param array $uploadedFiles
     */
    public function __construct($server = [], $cookieParams = [], $uploadedFiles = [])
    {
        $this->serverParams = $server;
        $this->cookieParams = $cookieParams;
        $this->uploadedFiles = $uploadedFiles;
        if (isset($_GET)) {
            $this->queryParams = $_GET;
        } else {
            if (isset($server['QUERY_STRING'])) {
                parse_str($server['QUERY_STRING'], $this->queryParams);
            }
        };

        if (isset($_POST)) {
            $this->parsedBody = $_POST;
        }
        $uri = $this->getUriString();
        $method = isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : '';
        $headers = Helper::getPsrServerHeaders();
        $body = 'php://memory';
        parent::__construct($uri, $method, $headers, $body);
    }

    private function getUriString()
    {
        return (isset($this->serverParams['HTTPS']) && $this->serverParams['HTTPS'] === 'on' ? "https" : "http") . '://' . $this->serverParams['HTTP_HOST'] . $this->serverParams['REQUEST_URI'];
    }

    public function getServerParams()
    {
        return $this->serverParams;
    }

    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies)
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query)
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data)
    {
        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        return $default;
    }

    public function withAttribute($name, $value)
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    public function withoutAttribute($name)
    {
        unset($this->attributes[$name]);
    }
}