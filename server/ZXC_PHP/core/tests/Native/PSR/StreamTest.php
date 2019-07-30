<?php

class StreamTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $stream = new \ZXC\Native\PSR\Stream('php://memory', 'r');
        $this->assertSame('php://memory', $stream->getSourceStream());
    }

    public function testAttach()
    {
        $tmp = 'php://temp';
        $stream = new \ZXC\Native\PSR\Stream();
        $stream->attach($tmp, 'r');
        $this->assertSame($tmp, $stream->getSourceStream());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAttachException()
    {
        $stream = new \ZXC\Native\PSR\Stream();
        $this->assertSame('php://memory', $stream->getSourceStream());
        $stream->attach(123, 'a');
    }

    public function test__toString()
    {
        $message = 'Hello stream';
        $stream = new \ZXC\Native\PSR\Stream('php://memory', 'w+');
        $contentString = $stream->getContents();
        $this->assertSame('', $contentString);
        $stream->write($message);
        $stream->rewind();
        $contentString = $stream->getContents();
        $this->assertSame($message, $contentString);
        $string = $stream->__toString();
        $this->assertSame($message, $string);
    }

    public function testClose()
    {
        $message = 'Hello stream';
        $stream = new \ZXC\Native\PSR\Stream('php://memory', 'w+');
        $stream->write($message);
        $param = 'resource';
        $this->assertAttributeNotEmpty($param, $stream);
        $stream->close();
        $this->assertAttributeEmpty($param, $stream);
        $this->assertSame('', (string)$stream);
    }

    public function testDetach()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        $resource = fopen($tmpFile, 'w+');
        $stream = new \ZXC\Native\PSR\Stream($resource);
        $this->assertSame($resource, $stream->detach());
        $this->assertAttributeEmpty('resource', $stream);
        $this->assertAttributeEmpty('sourceStream', $stream);
    }

    public function testGetSize()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        $resource = fopen($tmpFile, 'w+');
        $stream = new \ZXC\Native\PSR\Stream($resource);
        $stream->write('ABCD');
        $size = $stream->getSize();
        $this->assertSame(4, $size);
        $stream->close();
    }

    public function testTell()
    {
        $stream = new \ZXC\Native\PSR\Stream('php://memory', 'w+');
        $result = $stream->tell();
        $this->assertSame(0, $result);
        $stream->write('ABCD');
        $stream->seek(2);
        $result = $stream->tell();
        $this->assertSame(2, $result);
        $content = $stream->getContents();
        $this->assertSame('CD', $content);
    }

    public function testEof()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        file_put_contents($tmpFile, 'ABCD');
        $resource = fopen($tmpFile, 'rb');
        $stream = new \ZXC\Native\PSR\Stream($resource);
        $stream->seek(2);
        $this->assertFalse($stream->eof());
        $contents = '';
        while (!feof($resource)) {
            $contents .= fread($resource, 2);
        }
        $this->assertSame('CD', $contents);
        $this->assertTrue($stream->eof());
    }

    public function testIsSeekable()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        file_put_contents($tmpFile, 'ABCD');
        $resource = fopen($tmpFile, 'r');
        $stream = new \ZXC\Native\PSR\Stream($resource);
        $this->assertTrue($stream->isSeekable());
    }

    public function testSeek()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        file_put_contents($tmpFile, 'ABCD');
        $resource = fopen($tmpFile, 'r');
        $stream = new \ZXC\Native\PSR\Stream($resource);
        $stream->seek(1);
        $this->assertSame(1, $stream->tell());
        $stream->seek(1, SEEK_CUR);
        $this->assertSame(2, $stream->tell());
        $stream->seek(-1, SEEK_END);
        $this->assertSame(3, $stream->tell());
        $this->assertSame('D', $stream->getContents());
        $stream->close();
    }

    public function testRewind()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        file_put_contents($tmpFile, 'ABCD');
        $resource = fopen($tmpFile, 'r');
        $stream = new \ZXC\Native\PSR\Stream($resource);
        $stream->seek(2);
        $stream->rewind();
        $this->assertSame(0, $stream->tell());
    }

    public function testIsWritable()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        file_put_contents($tmpFile, 'ABCD');
        $resource = fopen($tmpFile, 'r');
        $stream = new \ZXC\Native\PSR\Stream($resource);
        $this->assertFalse($stream->isWritable());
        $stream->close();
        $resource = fopen($tmpFile, 'w');
        $stream = new \ZXC\Native\PSR\Stream($resource);
        $this->assertTrue($stream->isWritable());
    }

    public function testWrite()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        $stream = new \ZXC\Native\PSR\Stream($tmpFile, 'w+b');
        $stream->write('ABCD');
        $stream->seek(4);
        $stream->write('123');
        $stream->rewind();
        $this->assertSame('ABCD123', $stream->getContents());

        $stream->seek(-2, SEEK_CUR);
        $stream->write('456');

        $stream->seek(-8, SEEK_CUR);
        $this->assertSame('ABCD1456', $stream->getContents());
    }

    public function testIsReadable()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        $stream = new \ZXC\Native\PSR\Stream($tmpFile, 'wb');
        $this->assertFalse($stream->isReadable());
        $stream->close();
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        $stream = new \ZXC\Native\PSR\Stream($tmpFile, 'w+');
        $this->assertTrue($stream->isReadable());
    }

    public function testRead()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        file_put_contents($tmpFile, 'ABCD');
        $stream = new \ZXC\Native\PSR\Stream($tmpFile, 'rb');
        $read = $stream->read(2);
        $this->assertSame('AB', $read);
        $stream->seek(-2, SEEK_CUR);
        $read = $stream->read(4);
        $this->assertSame('ABCD', $read);
    }

    public function testGetContents()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        file_put_contents($tmpFile, 'ABCD');
        $stream = new \ZXC\Native\PSR\Stream($tmpFile, 'rb');
        $this->assertSame('ABCD', $stream->getContents());
    }

    public function testGetMetadata()
    {
        $mode = 'rb';
        $tmpFile = tempnam(sys_get_temp_dir(), 'zxc');
        file_put_contents($tmpFile, 'ABCD');
        $stream = new \ZXC\Native\PSR\Stream($tmpFile, $mode);
        $this->assertSame($mode, $stream->getMetadata('mode'));
        $this->assertFalse($stream->getMetadata('mode') === 'wb');
    }
}