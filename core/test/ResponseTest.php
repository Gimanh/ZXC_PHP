<?php

use PHPUnit\Framework\TestCase;

$dir = __DIR__;
$config = [];
$file = $dir . '/../index.php';
if (file_exists($file)) {
    require_once $file;
}

class ResponseTest extends TestCase
{
    public function testAddHeaders()
    {

        $header1 = [
            'Content-Type' => ['application/json']
        ];
        $header2 = [
            'Content-Type' => ['text/xml']
        ];
        $header3 = [
            'Content-Type' => ['application/json', 'text/xml'],
            'Cache-Control' => ['no-cache, must-revalidate'],
            'Expires' => ['Sat, 26 Jul 1997 05:00:00 GMT']
        ];
        $response = \ZXC\Native\HTTP\Response::getInstance();
        $response::addHeaders($header1);
        $this->assertSame($response::getHeaders(), ['Content-Type' => ['application/json']]);
        $response::addHeaders($header2);
        $this->assertSame($response::getHeaders(), ['Content-Type' => ['application/json', 'text/xml']]);
        $this->assertTrue($response::addHeaders($header3));
        $this->assertSame($response::getHeaders(),
            [
                'Content-Type' => ['application/json', 'text/xml', 'application/json', 'text/xml'],
                'Cache-Control' => ['no-cache, must-revalidate'],
                'Expires' => ['Sat, 26 Jul 1997 05:00:00 GMT']
            ]
        );
        $this->assertFalse($response::addHeaders([]));
        $response::deleteAllHeaders();
    }

    public function testDeleteHeader()
    {
        $response = \ZXC\Native\HTTP\Response::getInstance();
        $header3 = [
            'Content-Type' => ['application/json', 'text/xml', 'application/pdf'],
            'Cache-Control' => ['no-cache, must-revalidate'],
            'Expires' => ['Sat, 26 Jul 1997 05:00:00 GMT']
        ];
        $this->assertTrue($response::addHeaders($header3));
        $this->assertTrue($response::deleteHeader('Expires'));
        $this->assertSame($response::getHeaders(),
            [
                'Content-Type' => ['application/json', 'text/xml', 'application/pdf'],
                'Cache-Control' => ['no-cache, must-revalidate']
            ]
        );
        $this->assertFalse($response::deleteHeader('Expires'));
        $response::deleteAllHeaders();
    }

    public function testDeleteHeaderValue()
    {
        $response = \ZXC\Native\HTTP\Response::getInstance();
        $header1 = [
            'Content-Type' => ['application/json', 'text/xml', 'application/pdf'],
            'Cache-Control' => ['no-cache, must-revalidate'],
            'Expires' => ['Sat, 26 Jul 1997 05:00:00 GMT']
        ];

        $response::addHeaders($header1);

        $this->assertTrue($response::deleteHeaderValue('Content-Type', ['application/pdf']));

        $this->assertSame($response::getHeaders(),
            [
                'Content-Type' => ['application/json', 'text/xml'],
                'Cache-Control' => ['no-cache, must-revalidate'],
                'Expires' => ['Sat, 26 Jul 1997 05:00:00 GMT']
            ]
        );

        $this->assertTrue($response::deleteHeaderValue('Content-Type', ['text/xml']));

        $this->assertTrue($response::deleteHeaderValue('Content-Type', ['application/json']));

        $this->assertFalse($response::existHeader('Content-Type'));

        $response::deleteAllHeaders();
    }

    public function testSetResponseHttpCode()
    {
        $response = \ZXC\Native\HTTP\Response::getInstance();
        $this->assertTrue($response::setResponseHttpCode(404));
    }
}