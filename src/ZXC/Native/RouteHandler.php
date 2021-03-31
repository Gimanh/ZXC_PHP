<?php


namespace ZXC\Native;


use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ZXC\Interfaces\Psr\Http\Message\ResponseInterface;
use ZXC\Interfaces\Psr\Server\MiddlewareInterface;
use ZXC\Interfaces\Psr\Server\RequestHandlerInterface;

class RouteHandler
{
    const UNKNOWN_HANDLER = -1;

    /**
     * Handler format
     * Class extends "RequestHandlerInterface" and has implemented "process" method
     */
    const PSR_MIDDLEWARE = 1;

    /**
     * Handler format
     * Class extends "RequestHandlerInterface" and has implemented "handler" method
     */
    const PSR_HANDLER = 2;

    /**
     * Handler format
     * Namespace\ClassName:methodName
     */
    const ZXC_HANDLER = 3;

    /**
     * Handler format
     * Class has implemented "__invoke" method
     */
    const INVOKE_HANDLER = 4;

    /**  @var string */
    protected $handler = '';

    /** @var array */
    protected $args = [];

    /** @var string */
    protected $class = '';

    /** @var string */
    protected $method = '';

    protected $handlerType;

    public function __construct(string $handler, array $args)
    {
        $this->handler = $handler;
        $this->args = $args;
        $this->handlerType = $this->detectHandlerType();
    }

    public function detectHandlerType()
    {
        $data = explode(':', $this->handler);
        $this->class = $data[0];

        if (count($data) === 2) {
            $this->method = $data[1];
            return self::ZXC_HANDLER;
        }

        if (is_a($this->class, MiddlewareInterface::class, true)) {
            $this->method = 'process';
            return self::PSR_MIDDLEWARE;
        }

        if (is_a($this->class, RequestHandlerInterface::class, true)) {
            $this->method = 'handle';
            return self::PSR_MIDDLEWARE;
        }

        if (method_exists($this->class, '__invoke')) {
            $this->method = '__invoke';
            return self::INVOKE_HANDLER;
        }
        return self::UNKNOWN_HANDLER;
    }

    public function call(): ResponseInterface
    {
        if ($this->handlerType === self::UNKNOWN_HANDLER) {
            throw new InvalidArgumentException('Unknown handler type');
        }
        $instance = new $this->class;
        return call_user_func_array([$instance, $this->method], $this->args);

    }
}
