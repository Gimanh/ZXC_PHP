<?php
///**
// * Created by PhpStorm.
// * User: msowers
// * Date: 3/31/15
// * Time: 10:57 AM
// */
//
//use ZXC\Modules\Mailer\Tx\Exceptions\SMTPException;
//use ZXC\Modules\Mailer\Tx\Message;
//use ZXC\Modules\Mailer\Tx\SMTP;
//
///**
// * Class SMTPTest
// * @package Tests
// *
// * This test set requires the use of an open SMTP server mock.  Currently, I'm using FakeSMTPServer
// *
// */
//class SMTPTest extends TestCaseTx
//{
//
//    /**
//     * @var SMTP
//     */
//    protected $smtp;
//
//    /**
//     * @var  Message
//     */
//    protected $message;
//
//    public function setup()
//    {
//        $this->message = new Message();
//        $this->message
//            ->setFrom(self::FROM_NAME, self::FROM_EMAIL) // your name, your email
//            //->setFakeFrom('Hello', 'bot@fakeemail.com') // a fake name, a fake email
//            ->addTo(self::TO_NAME, self::TO_EMAIL)
//            ->addCc(self::CC_NAME, self::CC_EMAIL)
//            ->addBcc(self::BCC_NAME, self::BCC_EMAIL)
//            ->setSubject('Test SMTP ' . time())
//            ->setBody('<h1>for test</h1>')
//            ->addAttachment('test', __FILE__);
//        usleep(self::DELAY);
//    }
//
//    /**
//     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CodeException
//     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CryptoException
//     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\SMTPException
//     */
//    public function testSend()
//    {
//        $this->smtp = new SMTP();
//        $this->smtp
//            ->setServer(self::SERVER, self::PORT)
//            ->setAuth(self::USER, self::PASS);
//
//        $status = $this->smtp->send($this->message);
//        $this->assertTrue($status);
//        usleep(self::DELAY);
//    }
//
////    /**
////     * @throws SMTPException
////     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CodeException
////     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CryptoException
////     */
////    public function testTLSSend()
////    {
////        $this->smtp = new SMTP();
////        $this->smtp
////            ->setServer(self::SERVER, self::PORT_TLS, 'tls')
////            ->setAuth(self::USER, self::PASS);
////
////        $status = $this->smtp->send($this->message);
////        $this->assertTrue($status);
////        usleep(self::DELAY);
////    }
//
////    /**
////     * @throws SMTPException
////     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CodeException
////     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CryptoException
////     */
////    public function testTLSv10Send()
////    {
////        $this->smtp = new SMTP();
////        $this->smtp
////            ->setServer(self::SERVER, self::PORT_TLS, 'tlsv1.0')
////            ->setAuth(self::USER, self::PASS);
////
////        $status = $this->smtp->send($this->message);
////        $this->assertTrue($status);
////        usleep(self::DELAY);
////    }
////
////    /**
////     * @throws SMTPException
////     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CodeException
////     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CryptoException
////     */
////    public function testTLSv11Send()
////    {
////        $this->smtp = new SMTP();
////        $this->smtp
////            ->setServer(self::SERVER, self::PORT_TLS, 'tlsv1.1')
////            ->setAuth(self::USER, self::PASS);
////
////        $status = $this->smtp->send($this->message);
////        $this->assertTrue($status);
////        usleep(self::DELAY);
////    }
////
////    /**
////     * @throws SMTPException
////     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CodeException
////     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CryptoException
////     */
////    public function testTLSv12Send()
////    {
////        $this->smtp = new SMTP();
////        $this->smtp
////            ->setServer(self::SERVER, self::PORT_TLS, 'tlsv1.2')
////            ->setAuth(self::USER, self::PASS);
////
////        $status = $this->smtp->send($this->message);
////        $this->assertTrue($status);
////        usleep(self::DELAY);
////    }
//
//    /**
//     * @throws SMTPException
//     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CodeException
//     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CryptoException
//     * @expectedException ZXC\Modules\Mailer\Tx\Exceptions\SMTPException
//     */
//    public function testConnectSMTPException()
//    {
////        $this->expectException(SMTPException::class);
//        $this->smtp = new SMTP();
//        $this->smtp
//            ->setServer('localhost', "99999", null)
//            ->setAuth('none', 'none');
//
//        $this->smtp->send($this->message);
//        usleep(self::DELAY);
//    }
//
//}
