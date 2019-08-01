<?php

use ZXC\Native\PSR\Uri;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.04.2019
 * Time: 15:12
 */
class UriTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $uri = new Uri('https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment');
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('userName:password', $uri->getUserInfo());
        $this->assertSame('local.example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('userName:password@local.example.com:8080', $uri->getAuthority());
        $this->assertSame('/spa', $uri->getPath());
        $this->assertSame('paramName=paramValue', $uri->getQuery());
        $this->assertSame('fragment', $uri->getFragment());
    }

    public function testWithScheme()
    {
        $address = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $uri = new Uri($address);
        $new = $uri->withScheme('http');
        $this->assertSame('http', $new->getScheme());
        $addressNew = 'http://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $newString = (string)$new;
        $this->assertSame($addressNew, $newString);
    }

    public function testWithUserInfo()
    {
        $address = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $uri = new Uri($address);
        $new = $uri->withUserInfo('user2', 'password2');
        $this->assertSame('user2:password2', $new->getUserInfo());
        $addressNew = 'https://user2:password2@local.example.com:8080/spa?paramName=paramValue#fragment';
        $newString = (string)$new;
        $this->assertSame($addressNew, $newString);
    }

    public function testWithHost()
    {
        $address = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $uri = new Uri($address);
        $new = $uri->withHost('example3.com');
        $this->assertSame('example3.com', $new->getHost());
        $addressNew = 'https://userName:password@example3.com:8080/spa?paramName=paramValue#fragment';
        $newString = (string)$new;
        $this->assertSame($addressNew, $newString);
    }

    public function testWithPort()
    {
        $address = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $uri = new Uri($address);
        $new = $uri->withPort('9090');
        $this->assertSame('9090', $new->getPort());
        $addressNew = 'https://userName:password@local.example.com:9090/spa?paramName=paramValue#fragment';
        $newString = (string)$new;
        $this->assertSame($addressNew, $newString);
    }

    public function testWithPath()
    {
        $address = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $uri = new Uri($address);
        $new = $uri->withPath('/spa/account');
        $this->assertSame('/spa/account', $new->getPath());
        $addressNew = 'https://userName:password@local.example.com:8080/spa/account?paramName=paramValue#fragment';
        $newString = (string)$new;
        $this->assertSame($addressNew, $newString);
    }

    public function testWithQuery()
    {
        $address = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $uri = new Uri($address);
        $new = $uri->withQuery('p1=p2&p4=p5');
        $this->assertSame('p1=p2&p4=p5', $new->getQuery());
        $addressNew = 'https://userName:password@local.example.com:8080/spa?p1=p2&p4=p5#fragment';
        $newString = (string)$new;
        $this->assertSame($addressNew, $newString);
    }

    public function testWithFragment()
    {
        $address = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $uri = new Uri($address);
        $new = $uri->withFragment('newFragment');
        $this->assertSame('newFragment', $new->getFragment());
        $addressNew = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#newFragment';
        $newString = (string)$new;
        $this->assertSame($addressNew, $newString);
    }

    public function test__toString()
    {
        $address = 'https://userName:password@local.example.com:8080/spa?paramName=paramValue#fragment';
        $uri = new Uri($address);
        $this->assertSame($address, (string)$uri);
    }

}