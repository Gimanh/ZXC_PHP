<?php

namespace ZXC\Native\HTTP;

use ZXC\ZXC;
use ZXC\Native\Helper;
use ZXC\Traits\Module;
use ReflectionException;
use ZXC\Interfaces\IModule;
use Psr\Http\Message\ResponseInterface;

class CORS implements IModule
{
    use Module;

    protected $maxAge = '';
    protected $allowOrigin = '';
    protected $credentials = 'false';
    protected $allowedHeaders = [];
    protected $allowedMethods = [];

    /**
     * Initialize class with config
     * @param array $config
     * @return bool
     */
    public function initialize(array $config = null)
    {
        if (isset($config['allowOrigin'])) {
            $this->allowOrigin = $config['allowOrigin'];
        }
        if (isset($config['credentials'])) {
            $this->credentials = $config['credentials'];
        }
        if (isset($config['allowedHeaders'])) {
            $this->allowedHeaders = $config['allowedHeaders'];
        }
        if (isset($config['maxAge'])) {
            $this->maxAge = $config['maxAge'];
        }
        if (isset($config['allowedMethods'])) {
            $this->allowedMethods = $config['allowedMethods'];
        }
        return true;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws ReflectionException
     * @method setCorsResponse
     */
    public function setCorsResponse(ResponseInterface $response)
    {
        return $response
            ->withHeader('Access-Control-Max-Age', $this->maxAge)
            ->withHeader('Access-Control-Allow-Origin', $this->getAllowOrigin())
            ->withHeader('Access-Control-Allow-Headers', $this->allowedHeaders)
            ->withHeader('Access-Control-Allow-Methods', $this->allowedMethods)
            ->withHeader('Access-Control-Allow-Credentials', $this->credentials);
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public function getAllowOrigin()
    {
        return Helper::callCallback($this->allowOrigin, ZXC::instance()->getRequest());
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }


}
