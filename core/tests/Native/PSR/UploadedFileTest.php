<?php

use ZXC\Native\PSR\Stream;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.04.2019
 * Time: 15:44
 */
class UploadedFileTest extends PHPUnit\Framework\TestCase
{
    /**
     * @method testConstructorException
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid $clientMediaType argument must be string
     */
    public function testConstructorException()
    {
        new \ZXC\Native\PSR\UploadedFile('path', 0, UPLOAD_ERR_OK, 'fName');
    }

    /**
     * @method testConstructorException
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid $clientFilename argument must be string
     */
    public function testConstructorExceptionFileName()
    {
        new \ZXC\Native\PSR\UploadedFile('path', 0, UPLOAD_ERR_OK);
    }

    public function testGetStream()
    {
        $stream = new Stream('php://temp', 'wb+');
        $uf = new \ZXC\Native\PSR\UploadedFile($stream, 0, UPLOAD_ERR_OK, 'fileName', 'mediaType');
        $this->assertTrue($uf->getStream() instanceof \Psr\Http\Message\StreamInterface);
    }

    /**
     * @method testMoveTo
     * @expectedException \RuntimeException
     */
    public function testMoveTo()
    {
        $content = 'Some string';
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($content);
        $upload = new \ZXC\Native\PSR\UploadedFile($stream, 0, UPLOAD_ERR_OK, 'fileName', 'mediaType');
        $targetPath = tempnam(sys_get_temp_dir(), 'zxc');
        $upload->moveTo($targetPath);
        $contents = file_get_contents($targetPath);
        $this->assertSame($contents, (string)$stream);
        //\RuntimeException moved is true
        $upload->moveTo($targetPath);
    }

    /**
     * Using rename
     * @method testMoveToSapi
     */
    public function testMoveToSapi()
    {
        $content = 'Some string';
        $targetPathSource = sys_get_temp_dir() . 'zxc' . uniqid();
        file_put_contents($targetPathSource, $content);
        $upload = new \ZXC\Native\PSR\UploadedFile($targetPathSource, 0, UPLOAD_ERR_OK, 'fileName', 'mediaType');
        $targetPath = sys_get_temp_dir() . 'zxc' . uniqid();
        $upload->setSapi('cli');
        $upload->moveTo($targetPath);
        $contents = file_get_contents($targetPath);
        $this->assertSame($contents, $content);
    }

    /**
     * Using move_uploaded_file
     * @method testMoveToString
     * @expectedException \RuntimeException
     */
    public function testMoveToString()
    {
        $content = 'Some string';
        $targetPathSource = sys_get_temp_dir() . 'zxc' . uniqid();
        file_put_contents($targetPathSource, $content);
        $upload = new \ZXC\Native\PSR\UploadedFile($targetPathSource, 0, UPLOAD_ERR_OK, 'fileName', 'mediaType');
        $targetPath = sys_get_temp_dir() . '/http-' . uniqid();
        $upload->setSapi('cgi');
        $upload->moveTo($targetPath);
    }

    public function testGetSize()
    {
        $uf = new \ZXC\Native\PSR\UploadedFile('path', 123, UPLOAD_ERR_OK, 'fileName', 'mediaType');
        $this->assertSame(123, $uf->getSize());
    }

    public function testGetError()
    {
        $uf = new \ZXC\Native\PSR\UploadedFile('path', 123, UPLOAD_ERR_CANT_WRITE, 'fileName', 'mediaType');
        $this->assertSame(UPLOAD_ERR_CANT_WRITE, $uf->getError());
    }

    public function testGetClientFilename()
    {
        $uf = new \ZXC\Native\PSR\UploadedFile('path', 123, UPLOAD_ERR_CANT_WRITE, 'fileName', 'mediaType');
        $this->assertSame('fileName', $uf->getClientFilename());
    }

    public function testGetClientMediaType()
    {
        $uf = new \ZXC\Native\PSR\UploadedFile('path', 123, UPLOAD_ERR_CANT_WRITE, 'fileName', 'mediaType');
        $this->assertSame('mediaType', $uf->getClientMediaType());
    }
}