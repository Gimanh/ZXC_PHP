<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 07/12/2018
 * Time: 00:25
 */

class MessageTest extends \PHPUnit\Framework\TestCase
{
    public function testGetProtocolVersion()
    {
        $message = new \ZXC\Native\PSR\Message();
        $version = $message->getProtocolVersion();
        $this->assertSame('1.1', $version);
    }

    public function testWithProtocolVersion()
    {
        $message = new \ZXC\Native\PSR\Message();
        $newMessage = $message->withProtocolVersion('1.0');
        $version = $newMessage->getProtocolVersion();
        $this->assertSame('1.0', $version);
        $this->isInstanceOf('\Psr\Http\Message\MessageInterface');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid HTTP version
     */
    public function testWithProtocolVersionException()
    {
        $message = new \ZXC\Native\PSR\Message();
        $message->withProtocolVersion('1.3');
    }

    public function testGetHeaders()
    {
        $message = new \ZXC\Native\PSR\Message();
        $headers = $message->getHeaders();
        $this->assertSame([], $headers);
    }

    public function testHasHeader()
    {
        $message = new \ZXC\Native\PSR\Message();
        $hasHeaders = $message->hasHeader('content-type');
        $this->assertFalse($hasHeaders);
    }
}