<?php

namespace ZXC\Native\HTTP;

use Exception;
use ZXC\Traits\Module;
use ZXC\Interfaces\IModule;
use ZXC\Patterns\Singleton;
use InvalidArgumentException;

class Session implements IModule
{
    use Singleton, Module;

    protected $version = '0.0.1';
    protected $lifeTime;
    private $session;
    private $prefix;
    private $path;
    private $domain;
    private $name;
    private $secure;
    private $httpOnly = true;
    private $sessionId;

    /**
     * @param array $config
     * @return bool
     * @throws Exception
     */
    public function initialize(array $config = null)
    {
        if (!isset($config['time'])) {
            $this->lifeTime = 7200;
        } else {
            $this->lifeTime = $config['time'];
        }
        if (!isset($config['path'])) {
            $this->path = '/';
        } else {
            $this->path = $config['path'];
        }
        if (!isset($config['domain'])) {
            throw new InvalidArgumentException('Session::init domain field not found in config');
        } else {
            $this->domain = $config['domain'];
        }
        if (!isset($config['name'])) {
            //if session.auto_start is enabled by default you must set session name in php.ini
            $this->name = 'zxc';
        } else {
            $this->name = $config['name'];
        }

        if (isset($config['prefix']) && is_string($config['prefix'])) {
            $this->prefix = $config['prefix'];
        } else {
            $this->prefix = 'zxc';
        }

        if (isset($config['secure'])) {
            $this->secure = $config['secure'];
        } else {
            $this->secure = false;
        }

        if (isset($config['httpOnly'])) {
            $this->httpOnly = $config['httpOnly'];
        } else {
            $this->httpOnly = true;
        }
        session_name($this->name);
        session_set_cookie_params(($this->lifeTime * 60), $this->path, $this->domain, $this->secure, $this->httpOnly);
        $this->start();
        $this->session = &$_SESSION;
        $this->sessionId = session_id();
        return true;
    }

    public function set($key, $val)
    {
        if (empty($key) || empty($val)) {
            return false;
        }
        return $this->session[$this->prefix][$key] = $val;
    }

    public function get($key)
    {
        if (isset($this->session[$this->prefix][$key])) {
            return $this->session[$this->prefix][$key];
        }
        return false;
    }

    public function delete($key)
    {
        if (isset($this->session)) {
            unset($this->session[$this->prefix][$key]);
            return true;
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public function start()
    {
        if (session_status() == PHP_SESSION_NONE && !isset($_SESSION)) {
            /*if (!isset($_SESSION)) {
                if (PHP_SAPI === 'cli') {
                    $_SESSION = [];
                }
            } else*/
            if (!headers_sent($filename, $linenum)) {
                if (!session_start()) {
                    throw new Exception(__METHOD__ . 'session_start failed.');
                }
            } else {
                throw new Exception(
                    __METHOD__ . 'Session started after headers sent.');
            }
        }
    }

    public function clear()
    {
        $this->session[$this->prefix] = [];
    }

    public function isEnabled()
    {
        return session_status();
    }

    public function destroy()
    {
        if (isset($_SESSION)) {
            unset($_SESSION[$this->prefix]);
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @return mixed
     */
    public function getLifeTime()
    {
        return $this->lifeTime;
    }

    public function deleteSessionCookie()
    {
        setcookie($this->name, "", time() - 3600, '/');
    }
}