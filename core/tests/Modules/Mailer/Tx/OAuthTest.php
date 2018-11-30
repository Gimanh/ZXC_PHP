<?php
//
//use ZXC\Modules\Mailer\Tx\Mailer;
//
//class OAuthTest extends TestCaseTx
//{
//    /**
//     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CodeException
//     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\CryptoException
//     * @throws \ZXC\Modules\Mailer\Tx\Exceptions\SMTPException
//     * @expectedException \ZXC\Modules\Mailer\Tx\Exceptions\CodeException
//     */
//    public function testOAuth2()
//    {
////        if(!self::OAUTH_TOKEN){
////            return;
////        }
//        $mail = new Mailer();
////        $this->expectException(\ZXC\Modules\Mailer\Tx\Exceptions\CodeException::class);
//        $status = $mail->setServer(self::OAUTH_SERVER, self::OAUTH_PORT, 'tls')
//            ->setOAuth(self::OAUTH_TOKEN)
//            ->setFrom(self::OAUTH_FROM_NAME, self::OAUTH_FROM_EMAIL)
//            ->addTo(self::TO_NAME, self::TO_EMAIL)
//            ->addCc(self::CC_NAME, self::CC_EMAIL)
//            ->addBcc(self::BCC_NAME, self::BCC_EMAIL)
//            ->setSubject('Test Mailer OAuth2'. time())
//            ->setBody('Hi, boy')
//            ->addAttachment('test', __FILE__)
//            ->send();
//        $this->assertTrue($status);
//    }
//}
//
