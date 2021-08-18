<?php

namespace ZXC\Modules\Cors;

use ZXC\Interfaces\IModule;
use ZXC\Traits\Module;

class Cors implements IModule
{
    use Module;

    protected string $origin = '';

    protected bool $credentials = false;

    protected int $maxAge = 0;

    /**
     * @var string[]
     */
    protected array $headers = [];

    /**
     * @var string[]
     */
    protected array $methods = [];

    public function init(array $options = [])
    {
        $this->origin = $options['origin'] ?? '';
        $this->credentials = $options['credentials'] ?? false;
        $this->maxAge = $options['maxAge'] ?? 0;
        $this->headers = $options['headers'] ?? [];
        $this->methods = $options['methods'] ?? [];
    }

    public function getResponseHeaders(): array
    {
        if ($this->origin === '*') {
            $this->origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        }

        return [
            'Access-Control-Allow-Origin' => $this->origin,
            'Access-Control-Allow-Credentials' => $this->credentials,
            'Access-Control-Max-Age' => $this->maxAge,
            'Access-Control-Allow-Methods' => implode(',', $this->methods),
            'Access-Control-Allow-Headers' => implode(',', $this->headers)
        ];
    }
}
