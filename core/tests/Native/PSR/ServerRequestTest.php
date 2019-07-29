<?php

use ZXC\Native\PSR\UploadedFile;

/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 07/12/2018
 * Time: 00:09
 */
class ServerRequestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $serverRequest;
    protected $server;
    protected $cookie;
    protected $uploadedFiles;
    protected $files;

    protected function setUp()
    {
        $this->server = [
            'REQUEST_URI' => '/spa/user/qwerty?param=123&data=someData',
            'REQUEST_METHOD' => 'GET',
            'SERVER_PORT' => '443',
            'SERVER_NAME' => 'localhost',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'QUERY_STRING' => 'param=123&data=someData',
            'DOCUMENT_ROOT' => 'C:\inetpub\wwwroot',
            'HTTP_HOST' => 'localhost',
            'HTTP_ACCEPT' => 'text/html',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36',
            'HTTPS' => 'on',
            'REMOTE_ADDR' => '222.222.222.22',
            'REMOTE_PORT' => '51145',
            'SCRIPT_NAME' => '/spa/index.php',
            'SCRIPT_FILENAME' => 'G:\web\index.php',
            'PHP_SELF' => '/spa/index.php'
        ];
        $this->cookie = [
            'XDEBUG_SESSION' => 'PHPSTORM',
            'isLoggedIn' => 'plus'
        ];
        $this->uploadedFiles = [
            'files1' => new UploadedFile('php://temp', 0, UPLOAD_ERR_OK, 'fileName', 'application/pdf'),
        ];
        $this->files = [
            'file' => [
                'name' => 'index.js',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/php/kl342llkj',
                'error' => UPLOAD_ERR_OK,
                'size' => 123456789,
            ]
        ];
        $_SERVER = array_merge($_SERVER, $this->server);
        $_COOKIE = array_merge($_COOKIE, $this->cookie);
        $_FILES = array_merge($_FILES, $this->files);
        $this->serverRequest = new \ZXC\Native\PSR\ServerRequest($this->server, $this->cookie, $this->uploadedFiles);
    }

    public function testGetServerParams()
    {
        $this->assertSame($this->server, $this->serverRequest->getServerParams());
    }

    public function testGetCookieParams()
    {
        $this->assertSame($this->cookie, $this->serverRequest->getCookieParams());
    }

    public function testWithCookieParams()
    {
        $sR = $this->serverRequest->withCookieParams(['new' => 'new']);
        $this->assertSame(['new' => 'new'], $sR->getCookieParams());
    }

    public function testQueryParams()
    {
        $nsr = $this->serverRequest->withQueryParams(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $nsr->getQueryParams());
    }

    public function testGetUploadedFiles()
    {
        $this->assertSame($this->uploadedFiles, $this->serverRequest->getUploadedFiles());
    }

    public function testWithUploadedFiles()
    {
        $uploadedFiles = [
            'files' => new UploadedFile('php://temp', 0, UPLOAD_ERR_OK, 'fileName', 'application/pdf'),
        ];
        $nsr = $this->serverRequest->withUploadedFiles($uploadedFiles);
        $this->assertSame($uploadedFiles, $nsr->getUploadedFiles());
    }

    public function testParsedBody()
    {
        $nsr = $this->serverRequest->withParsedBody(['p1' => 'v1']);
        $this->assertSame(['p1' => 'v1'], $nsr->getParsedBody());
    }

    public function testAttributes()
    {
        $nsr1 = $this->serverRequest->withAttribute('a', 'b');
        $nsr2 = $nsr1->withAttribute('c', 'd');

        $this->assertSame('b', $nsr1->getAttribute('a'));
        $this->assertSame('d', $nsr2->getAttribute('c'));

        $nsr1->withoutAttribute('a');
        $this->assertNull($nsr1->getAttribute('a'));

        $attrs = $nsr2->getAttributes();
        $this->assertSame(['a' => 'b', 'c' => 'd'], $attrs);
    }
}