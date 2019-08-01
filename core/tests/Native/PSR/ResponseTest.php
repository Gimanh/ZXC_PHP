<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 10.04.2019
 * Time: 10:06
 */

class ResponseTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var \ZXC\Native\PSR\Response
     */
    protected $response;

    protected $correctHeaders = [
        'Content-Type' => ['image/gif', 'image/png', 'text/plain'],
        'Host' => ['localhost'],
        'Accept' => ['text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3'],
        'X-Original-Url' => ['/spa/user/qwe?asda=123']
    ];

    protected function setUp()
    {
        $body = new \ZXC\Native\PSR\Stream('php://memory', 'wb+');
        $this->response = new \ZXC\Native\PSR\Response(200, $this->correctHeaders, $body, '1.0');
    }

    /**
     * @method testConstructor
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid HTTP version
     */
    public function testConstructor()
    {
        new \ZXC\Native\PSR\Response(200, $this->correctHeaders, 'php://memory', '1.3');
    }

    public function testGetProtocolVersion()
    {
        $this->assertSame('1.0', $this->response->getProtocolVersion());
    }

    public function testWithProtocolVersion()
    {
        $new = $this->response->withProtocolVersion('1.0');
        $version = $new->getProtocolVersion();
        $this->assertSame('1.0', $version);
    }

    /**
     * @method testWithProtocolVersionException
     * @expectedException \InvalidArgumentException
     */
    public function testWithProtocolVersionException()
    {
        $this->response->withProtocolVersion('1.4');
    }

    public function testGetHeaders()
    {
        $headers = $this->response->getHeaders();
        $this->assertSame($this->correctHeaders, $headers);
    }

    public function testHasHeader()
    {
        $has = $this->response->hasHeader('HoSt');
        $this->assertTrue($has);
        $has = $this->response->hasHeader('X-OrigInaL-Url');
        $this->assertTrue($has);
        $no = $this->response->hasHeader('SomeHeaders');
        $this->assertFalse($no);
    }

    public function testGetHeader()
    {
        $hostHeaderValue = ['localhost'];
        $headerValue = $this->response->getHeader('host');
        $this->assertSame($hostHeaderValue, $headerValue);
        $headerValue = $this->response->getHeader('qwerty');
        $this->assertSame([], $headerValue);
    }

    public function testGetHeaderLine()
    {
        $expected = 'image/gif,image/png,text/plain';
        $line = $this->response->getHeaderLine('contenT-typE');
        $this->assertSame($expected, $line);
        $line = $this->response->getHeaderLine('qwerty');
        $this->assertSame('', $line);
    }

    public function testWithHeader()
    {
        $new = $this->response->withHeader('TOKEN-CUSTOM', '1234567890');
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
        $this->response->withHeader('TOKEN-CUSTOM', null);
    }

    public function testWithAddedHeader()
    {
        $new = $this->response->withAddedHeader('HOst', 'someHostName');
        $headerValue = $new->getHeader('host');
        $this->assertSame(['localhost', 'someHostName'], $headerValue);
        $new = $this->response->withAddedHeader('hostNew', 'qwerty');
        $headerValue = $new->getHeader('hostnew');
        $this->assertSame(['qwerty'], $headerValue);
    }

    public function testWithoutHeader()
    {
        $has = $this->response->hasHeader('host');
        $this->assertTrue($has);
        $new = $this->response->withoutHeader('HOst');
        $has = $new->hasHeader('host');
        $this->assertFalse($has);
        $new = $this->response->withoutHeader('HOst1');
        $has = $new->hasHeader('host1');
        $this->assertFalse($has);
    }

    public function testGetBody()
    {
        $stream = $this->response->getBody();
        $this->assertTrue($stream instanceof \Psr\Http\Message\StreamInterface);
    }

    public function testWithBody()
    {
        $innerStream = $this->response->getBody();
        $innerStream->write('Inner');
        $innerStream->rewind();
        $content = $innerStream->getContents();
        $this->assertSame('Inner', $content);
        $newStream = new \ZXC\Native\PSR\Stream('php://memory', 'wb+');
        $newStream->write('Hello');
        $new = $this->response->withBody($newStream);
        $newBodyStream = $new->getBody();
        $newBodyStream->rewind();
        $newContent = $newBodyStream->getContents();
        $this->assertSame('Hello', $newContent);
    }

    public function testGetStatusCode()
    {
        $this->assertSame(200, $this->response->getStatusCode());
    }

    public function testWithStatusCode()
    {
        $new = $this->response->withStatus(500);
        $this->assertSame(500, $new->getStatusCode());
    }
    public function testGetReasonPhrase(){
        $phrase = $this->response->getReasonPhrase();
        $this->assertSame('OK', $phrase);
    }
}