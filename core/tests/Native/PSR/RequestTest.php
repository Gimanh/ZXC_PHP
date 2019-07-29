<?php

use ZXC\Native\PSR\Uri;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 10.04.2019
 * Time: 8:05
 */
class RequestTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var \ZXC\Native\PSR\Request
     */
    protected $request;

    protected $correctHeaders = [
        'Content-Type' => ['image/gif', 'image/png', 'text/plain'],
        'Host' => ['localhost'],
        'Accept' => ['text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3'],
        'X-Original-Url' => ['/spa/user/qwe?asda=123']
    ];

    protected function setUp()
    {
        $uri = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $method = 'POST';
        $headers = [
            'Content-Type' => ['image/gif', 'image/png', 'text/plain'],
            'Host' => ['localhost'],
            'Accept' => ['text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3'],
            'X-Original-Url' => '/spa/user/qwe?asda=123'
        ];
        $body = new \ZXC\Native\PSR\Stream('php://memory', 'wb+');
        $this->request = new \ZXC\Native\PSR\Request($uri, $method, $headers, $body);
    }

    public function testConstructor()
    {
        $address = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $uri = new Uri($address);
        $req = new \ZXC\Native\PSR\Request($uri);
        $this->assertTrue(($req->getUri() instanceof \Psr\Http\Message\UriInterface));
        $req = new \ZXC\Native\PSR\Request($address);
        $this->assertTrue(($req->getUri() instanceof \Psr\Http\Message\UriInterface));
    }

    public function testGetProtocolVersion()
    {
        $version = $this->request->getProtocolVersion();
        $this->assertSame('1.1', $version);
    }

    public function testWithProtocolVersion()
    {
        $new = $this->request->withProtocolVersion('1.0');
        $version = $new->getProtocolVersion();
        $this->assertSame('1.0', $version);
    }

    /**
     * @method testWithProtocolVersionException
     * @expectedException \InvalidArgumentException
     */
    public function testWithProtocolVersionException()
    {
        $this->request->withProtocolVersion('1.4');
    }

    public function testGetHeaders()
    {
        $headers = $this->request->getHeaders();
        $this->assertSame($this->correctHeaders, $headers);
    }

    public function testHasHeader()
    {
        $has = $this->request->hasHeader('HoSt');
        $this->assertTrue($has);
        $has = $this->request->hasHeader('X-OrigInaL-Url');
        $this->assertTrue($has);
        $no = $this->request->hasHeader('SomeHeaders');
        $this->assertFalse($no);
    }

    public function testGetHeader()
    {
        $hostHeaderValue = ['localhost'];
        $headerValue = $this->request->getHeader('host');
        $this->assertSame($hostHeaderValue, $headerValue);
        $headerValue = $this->request->getHeader('qwerty');
        $this->assertSame([], $headerValue);
    }

    public function testGetHeaderLine()
    {
        $expected = 'image/gif,image/png,text/plain';
        $line = $this->request->getHeaderLine('contenT-typE');
        $this->assertSame($expected, $line);
        $line = $this->request->getHeaderLine('qwerty');
        $this->assertSame('', $line);
    }

    public function testWithHeader()
    {
        $new = $this->request->withHeader('TOKEN-CUSTOM', '1234567890');
        $header = $new->getHeader('TOKEN-cUSTOm');
        $this->assertSame(['1234567890'], $header);
        $new = $new->withHeader('TOKEN-CUSTOM2', ['1234567890']);
        $header = $new->getHeader('TOKEN-cUSTOm2');
        $this->assertSame(['1234567890'], $header);
        $new = $new->withHeader('HosT', ['example']);
        $newHost = $new->getHeader('host');
        $this->assertSame(['example'], $newHost);
    }

    /**
     * @method testWithHeaderException
     * @expectedException \InvalidArgumentException
     */
    public function testWithHeaderException()
    {
        $this->request->withHeader('TOKEN-CUSTOM', null);
    }

    public function testWithAddedHeader()
    {
        $new = $this->request->withAddedHeader('HOst', 'someHostName');
        $headerValue = $new->getHeader('host');
        $this->assertSame(['localhost', 'someHostName'], $headerValue);
        $new = $this->request->withAddedHeader('hostNew', 'qwerty');
        $headerValue = $new->getHeader('hostnew');
        $this->assertSame(['qwerty'], $headerValue);
    }

    public function testWithoutHeader()
    {
        $has = $this->request->hasHeader('host');
        $this->assertTrue($has);
        $new = $this->request->withoutHeader('HOst');
        $has = $new->hasHeader('host');
        $this->assertFalse($has);
        $new = $this->request->withoutHeader('HOst1');
        $has = $new->hasHeader('host1');
        $this->assertFalse($has);
    }

    public function testGetBody()
    {
        $stream = $this->request->getBody();
        $this->assertTrue($stream instanceof \Psr\Http\Message\StreamInterface);
    }

    public function testWithBody()
    {
        $innerStream = $this->request->getBody();
        $innerStream->write('Inner');
        $innerStream->rewind();
        $content = $innerStream->getContents();
        $this->assertSame('Inner', $content);
        $newStream = new \ZXC\Native\PSR\Stream('php://memory', 'wb+');
        $newStream->write('Hello');
        $new = $this->request->withBody($newStream);
        $newBodyStream = $new->getBody();
        $newBodyStream->rewind();
        $newContent = $newBodyStream->getContents();
        $this->assertSame('Hello', $newContent);
    }

    public function testGetRequestTarget()
    {
        $currentTarget = $this->request->getRequestTarget();
        $this->assertSame('/spa?paramName=paramValue', $currentTarget);
    }

    public function testWithRequestTarget()
    {
        $new = $this->request->withRequestTarget('/qwerty/app');
        $this->assertSame('/qwerty/app', $new->getRequestTarget());
    }

    public function testGetMethod()
    {
        $this->assertSame('POST', $this->request->getMethod());
    }

    public function testWithMethod()
    {
        $new = $this->request->withMethod('GET');
        $this->assertSame('GET', $new->getMethod());
    }

    public function testGetUri()
    {
        $uri = $this->request->getUri();
        $this->assertTrue($uri instanceof \Psr\Http\Message\UriInterface);
    }

    public function testWithUri()
    {
        $request = new \ZXC\Native\PSR\Request(null, 'POST', ['Host' => 'localhost.com']);
        $this->assertSame(['Host' => ['localhost.com']], $request->getHeaders());
        $address = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $uri = new Uri($address);
        $newReq = $request->withUri($uri);
        $this->assertSame('local.example.com:8080', $newReq->getHeaderLine('host'));

        $r = new \ZXC\Native\PSR\Request();
        $nr = $r->withUri($uri);
        $this->assertSame('local.example.com:8080', $nr->getHeaderLine('host'));
    }

    public function testWithUriPreserveHost()
    {
        $request = new \ZXC\Native\PSR\Request(null, 'POST', []);
        $address = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $uri = new Uri($address);
        $newReq = $request->withUri($uri, true);
        $this->assertSame('local.example.com:8080', $newReq->getHeaderLine('host'));


        $req1 = new \ZXC\Native\PSR\Request($uri, 'POST', ['Host' => 'localhost.com']);
        $this->assertSame(['Host' => ['localhost.com']], $req1->getHeaders());

        $address = 'https://userName:password@local.qwerty.com:8080/spa?paramName=paramValue#fragment';
        $uri = new Uri($address);

        $req2 = $req1->withUri($uri, true);
        $h = $req2->getHeaderLine('Host');
        $this->assertSame('localhost.com', $h);
    }

    public function testWithUriPreserveHostExample()
    {
        $uri1 = new Uri('https://userName:password@local.example1.com:8080/spa?paramName=paramValue#fragment');
        $uri2 = new Uri('https://userName:password@local.example2.com:8080/spa?paramName=paramValue#fragment');
        $uri3 = new Uri('https://userName:password@local.example3.com:8080/spa?paramName=paramValue#fragment');
        $uri4 = new Uri('https://userName:password@local.example4.com:8080/spa?paramName=paramValue#fragment');
        $r1 = new \ZXC\Native\PSR\Request();
        $r2 = new \ZXC\Native\PSR\Request();
        $r3 = new \ZXC\Native\PSR\Request(null,'POST', ['Host'=>'startHost']);
        $r4 = new \ZXC\Native\PSR\Request(null,'POST', ['Host'=>'startHost4']);

        $nr1 = $r1->withUri($uri1);
        $this->assertSame('local.example1.com:8080', $nr1->getHeaderLine('host'));

        $nr2 = $r2->withUri($uri2, true);
        $this->assertSame('local.example2.com:8080', $nr2->getHeaderLine('host'));

        $nr3 = $r3->withUri($uri3);
        $this->assertSame('local.example3.com:8080', $nr3->getHeaderLine('host'));

        $nr4 = $r4->withUri($uri4, true);
        $this->assertSame('startHost4', $nr4->getHeaderLine('Host'));
    }
}