<?php

use ZXC\Native\HTTP\Session;
use ZXC\Modules\Logger\Logger;
use ZXC\Native\ModulesManager;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 15.04.2019
 * Time: 9:26
 */
class ModulesManagerTest extends PHPUnit\Framework\TestCase
{
    public $modules = [
        'Session' => [
            'class' => '\ZXC\Native\HTTP\Session',
            'options' => [
                'prefix' => 'zxc_',
                'time' => 6200,
                'path' => '/',
                'domain' => 'zxc.com'
            ]
        ],
        'Logger' => [
            'class' => 'ZXC\Modules\Logger\Logger',
            'defer' => true,
            'options' => [
                /**
                 * current app debug lvl
                 */
                'applevel' => 'debug',
                'folder' => 'log',
                'file' => '/log_zxc_test.log',
                /**
                 * root if set true will load from ZXC_ROOT.'../log/log.log'
                 */
                'root' => true

            ]
        ],
        'DB' => [
            'class' => 'ZXC\Native\DB',
            'options' => [
                'dbname' => 'zxc',
                'dbtype' => 'pgsql',
                'host' => 'localhost',
                'port' => 5432,
                'user' => 'postgres',
                'password' => '123456'
            ]
        ],
        'Mailer' => [
            'class' => 'ZXC\Modules\Mailer\Mail',
            'options' => [
                'server' => 'smtp.mailtrap.io',
                'port' => 465,
                'ssl' => true,
                'user' => '7b12bd3165709b',
                'password' => '1de0bbd8c472c4',
                'from' => 'zxc_php',
                'fromEmail' => 'zxcphptestf@mail.com'
            ]
        ]
    ];

    /**
     * @method setUp
     * @throws ReflectionException
     */
    protected function setUp()
    {
        ModulesManager::destroy();
        ModulesManager::installModules($this->modules);
    }

    /**
     * @method tearDown
     * @throws ReflectionException
     */
    protected function tearDown()
    {
        /** @var Logger $deferLogger */
        $deferLogger = ModulesManager::getModule('logger');
        if ($deferLogger) {
            $file = $deferLogger->getFullLogFilePath();
            if (file_exists($file)) {
                unlink($file);
            }
        }
        ModulesManager::destroy();
    }

    /**
     * @method testInstallModules
     * @throws ReflectionException
     */
    public function testInstallModules()
    {
        $this->assertTrue(ModulesManager::installModules($this->modules));
        $this->assertSame($this->modules, ModulesManager::getModulesOptions());
        $this->assertSame(3, count(ModulesManager::getModulesInstances()));
    }

    public function testUninstallModules()
    {
        $uninstallModules = [
            'Session' => true
        ];
        $result = ModulesManager::uninstallModules($uninstallModules);
        $this->assertTrue($result);
        $result = ModulesManager::uninstallModules([]);
        $this->assertFalse($result);
    }

    /**
     * @method testGetModule
     * @throws ReflectionException
     */
    public function testGetModule()
    {
        $session = ModulesManager::getModule('session');
        $this->assertTrue(($session instanceof Session));
        $module = ModulesManager::getModule('module');
        $this->assertNull($module);
        /** @var Logger $deferLogger */
        $deferLogger = ModulesManager::getModule('logger');
        $this->assertTrue(($deferLogger instanceof Logger));
        unlink($deferLogger->getFullLogFilePath());
    }

    /**
     * @method testGetNewModule
     * @throws ReflectionException
     */
    public function testGetNewModule()
    {
        $sessionOld = ModulesManager::getModule('session');
        $sessionNew = ModulesManager::getNewModule('session',
            [
                'prefix' => 'domain_',
                'time' => 6200,
                'path' => '/',
                'domain' => 'domain.com'
            ]
        );
        $this->assertNotEquals($sessionOld, $sessionNew);
    }

    public function testHasModule()
    {
        $this->assertTrue(ModulesManager::hasModule('session'));
        $this->assertTrue(ModulesManager::hasModule('sesSioN'));
        $this->assertTrue(ModulesManager::hasModule('SESSION'));
        $this->assertFalse(ModulesManager::hasModule('MODULE'));
    }

    public function testDestroy()
    {

        $options = ModulesManager::getModulesOptions();
        $this->assertSame($this->modules, $options);
        $instances = ModulesManager::getModulesInstances();
        $this->assertSame(3, count($instances));
        ModulesManager::destroy();

        $options = ModulesManager::getModulesOptions();
        $this->assertEmpty($options);
        $instances = ModulesManager::getModulesInstances();
        $this->assertEmpty($instances);
    }

    /**
     * @method testGetModuleByClassName
     * @throws ReflectionException
     */
    public function testGetModuleByClassName()
    {
        $instance = ModulesManager::getModuleByClassName('ZXC\Modules\Logger\Logger');
        $this->assertTrue($instance instanceof ZXC\Modules\Logger\Logger);
    }
}