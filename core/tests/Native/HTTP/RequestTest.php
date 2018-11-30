<?php

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRequestInitializeException()
    {
        $server = [];
        $request = \ZXC\Native\HTTP\Request::getInstance();
        $request->initialize($server);
    }

    public function testRequestInitializeCheck()
    {
        $server = [];
        $server['HTTP_HOST'] = 'zxc:80';
        $server['SERVER_NAME'] = 'zxcserver';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_METHOD'] = 'POST';
        $server['REQUEST_URI'] = '/user?registerUser=%7B+%22password1%22%3A+%22123456%22%2C+%22password2%22%3A+%22123456%22%2C+%22login%22%3A%22head%22%2C+%22email%22%3A+%22head%40mail.ru%22+%7D&loginUser=%7B+%22password%22%3A+%22123456%22%2C+%22email%22%3A+%22head%40mail.ru%22%2C+%22remember%22%3A+true%7D';
        $server['SCRIPT_NAME'] = '/index.php';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $request = \ZXC\Native\HTTP\Request::getInstance();
        $request->initialize($server);
        $this->assertSame($request->getBaseRoute(), DIRECTORY_SEPARATOR);
        $this->assertSame($request->getPath(), '/user');

        $server['REQUEST_URI'] = '/zxc/user?registerUser=%7B+%22password1%22%3A+%22123456%22%2C+%22password2%22%3A+%22123456%22%2C+%22login%22%3A%22head%22%2C+%22email%22%3A+%22head%40mail.ru%22+%7D&loginUser=%7B+%22password%22%3A+%22123456%22%2C+%22email%22%3A+%22head%40mail.ru%22%2C+%22remember%22%3A+true%7D';
        $server['SCRIPT_NAME'] = '/zxc/index.php';
        $request->initialize($server);
        $this->assertSame($request->getBaseRoute(), '/zxc');
        $this->assertSame($request->getPath(), '/user');
    }

}