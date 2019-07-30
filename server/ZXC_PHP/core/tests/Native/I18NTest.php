<?php

class I18NTest extends PHPUnit\Framework\TestCase
{
    public function testT()
    {
        $configPhrases = [
            'email' => 'Почта',
            'confirm' => 'Подтверждение'
        ];
        \ZXC\Native\I18N::initialize($configPhrases);
        $hello = \ZXC\Native\I18N::t('email');
        $this->assertSame('Почта', $hello);
    }
}