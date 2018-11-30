<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 16/11/2018
 * Time: 23:46
 */

class ClassicTest extends \PHPUnit\Framework\TestCase
{
    private $login = 'head';
    private $email = 'test@mail.com';
    private $password = '12345678qQ1!';
    private $invalidPassword = '12345678qQ1123';
    private $undefinedEmail = 'testincorrect@mail.com';
    private $invalidEmail = 'someOyherEm#2%ail@mail.comqwj123jmvc i';
    private $shortInvalidLogin = 'asd';
    private $longInvalidLogin = '';
    private $testSecretKey = 'wSwmnvn*7&h3*90()@2';
    private $authStructure = null;

    public function __construct()
    {
        parent::__construct();
    }

    public static function setUpBeforeClass()
    {

    }

    public static function tearDownAfterClass()
    {

    }

    public function cleanUsersTableInTestAuth()
    {
        $structure = \ZXC\Modules\Auth\Auth::getStructure();
        $structure = $structure->withWhere(['email' => ['value' => $this->email]]);
        $deleteSql = $structure->delete();
        $db = \ZXC\Modules\Auth\Auth::getDb();
        $db->exec($deleteSql, $structure->getValues());
    }

    public function testRegistration()
    {
        $this->cleanUsersTableInTestAuth();
        $registrationData = [
            'login' => $this->login,
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];

        //register user
        $registrationOk = \ZXC\Modules\Auth\Auth::registration($registrationData);
        $this->assertTrue($registrationOk);

        //try register user with the same params
        $registrationNull = \ZXC\Modules\Auth\Auth::registration($registrationData);
        $this->assertNull($registrationNull);

        //try register with the same email but change login
        $registrationData = [
            'login' => 'testLogin',
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];
        $registrationNull = \ZXC\Modules\Auth\Auth::registration($registrationData);
        $this->assertNull($registrationNull);

        //try register with the same login but change email
        $registrationData = [
            'login' => $this->login,
            'email' => $this->undefinedEmail,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];
        $registrationNull = \ZXC\Modules\Auth\Auth::registration($registrationData);
        $this->assertNull($registrationNull);

        //try register with different password will be thrown exception with message "Passwords does not equal"
        $registrationData = [
            'login' => $this->login,
            'email' => $this->undefinedEmail,
            'password' => $this->invalidPassword,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];

        try {
            \ZXC\Modules\Auth\Auth::registration([]);
        } catch (Exception $exception) {
            $expectedExceptionText = '$data argument must isset next fields "mail", "password", "passwordRepeat", "agreement"';
            $this->assertSame($expectedExceptionText, $exception->getMessage());
        }

        try {
            \ZXC\Modules\Auth\Auth::registration($registrationData);
        } catch (Exception $exception) {
            $expectedExceptionText = 'Passwords does not equal';
            $this->assertSame($expectedExceptionText, $exception->getMessage());
        }

        $registrationData = [
            'login' => $this->login,
            'email' => $this->undefinedEmail,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => false
        ];
        try {
            \ZXC\Modules\Auth\Auth::registration($registrationData);
        } catch (Exception $exception) {
            $expectedExceptionText = 'Field agreement must be true';
            $this->assertSame($expectedExceptionText, $exception->getMessage());
        }

        $registrationData = [
            'login' => $this->login,
            'email' => $this->invalidEmail,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];
        try {
            \ZXC\Modules\Auth\Auth::registration($registrationData);
        } catch (Exception $exception) {
            $expectedExceptionText = 'Invalid email';
            $this->assertSame($expectedExceptionText, $exception->getMessage());
        }

        $registrationData = [
            'login' => $this->shortInvalidLogin,
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];
        try {
            \ZXC\Modules\Auth\Auth::registration($registrationData);
        } catch (Exception $exception) {
            $expectedExceptionText = 'Login must isset only character and have length between 4-20';
            $this->assertSame($expectedExceptionText, $exception->getMessage());
        }

        $registrationData = [
            'login' => $this->longInvalidLogin,
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];
        try {
            \ZXC\Modules\Auth\Auth::registration($registrationData);
        } catch (Exception $exception) {
            $expectedExceptionText = 'Login must isset only character and have length between 4-20';
            $this->assertSame($expectedExceptionText, $exception->getMessage());
        }

        $this->cleanUsersTableInTestAuth();
    }

    public function testRegistrationWithoutEmailConfirmation()
    {
        $provider = \ZXC\Modules\Auth\Auth::getProvider();
        $config = $provider->getConfig();
        $config['confirmEmail'] = false;
        $provider->initialize($config);

        $this->cleanUsersTableInTestAuth();
        $registrationData = [
            'login' => $this->login,
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];

        //register user
        $registrationOk = \ZXC\Modules\Auth\Auth::registration($registrationData);
        $this->assertTrue($registrationOk);

        $structure = $provider->getStructure();
        $structure = $structure->withWhere(['email' => ['value' => $this->email]]);
        $select = $structure->select();
        $result = $provider->getDb()->exec($select, $structure->getValues());
        $this->assertSame(0, $result[0]['block_status']);
        $this->assertSame(0, $result[0]['email_activation']);
        $this->assertNull($result[0]['email_activation_code']);

        $provider = \ZXC\Modules\Auth\Auth::getProvider();
        $config = $provider->getConfig();
        $config['confirmEmail'] = true;
        $provider->initialize($config);
    }

    public function testRegistrationWithWritingErrorLog()
    {
        $provider = \ZXC\Modules\Auth\Auth::getProvider();
        $oldConfig = $provider->getConfig();
        $config = $oldConfig;
        $config['logger']['options']['file'] = 'unit_test_log_with_smtp_error.log';
        $config['mailer']['options']['password'] = '25234';
        $config['confirmEmail'] = true;
        $provider->initialize($config);

        $this->cleanUsersTableInTestAuth();
        $registrationData = [
            'login' => $this->login,
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];

        //register user
        $registrationOk = \ZXC\Modules\Auth\Auth::registration($registrationData);
        $this->assertTrue($registrationOk);
        $this->assertTrue(file_exists($provider->getLogger()->getFullLogFilePath()));
        $fileContent = file_get_contents($provider->getLogger()->getFullLogFilePath());
        $position = (bool)strpos($fileContent, 'Unexpected return code');
        $this->assertTrue($position);
        unlink($provider->getLogger()->getFullLogFilePath());

        $structure = $provider->getStructure();
        $structure = $structure->withWhere(['email' => ['value' => $this->email]]);
        $select = $structure->select();
        $result = $provider->getDb()->exec($select, $structure->getValues());
        $this->assertSame(1, $result[0]['block_status']);
        $this->assertSame(0, $result[0]['email_activation']);
        $this->assertNotEmpty($result[0]['email_activation_code']);
        $provider->initialize($oldConfig);
        $this->cleanUsersTableInTestAuth();
    }

    /**
     * @throws Exception
     */
    public function testLogin()
    {
        $this->testRegistrationWithoutEmailConfirmation();
        $loginData = [
            'email' => $this->email,
            'password' => $this->password
        ];
        $loginSuccessResult = \ZXC\Modules\Auth\Auth::logIn($loginData);
        $testSecret = \ZXC\Native\Config::get('ZXC/Modules/Auth/options/options/token/secret_key');
        $decodedToken = \ZXC\Classes\Token::decode($loginSuccessResult['token'], $testSecret);
        $this->assertSame($this->email, $decodedToken['email']);
        $this->assertSame($this->login, $loginSuccessResult['login']);
        $this->cleanUsersTableInTestAuth();
    }

    /**
     * @throws Exception
     */
    public function testLoginWithIncorrectPassword()
    {
        $this->testRegistrationWithoutEmailConfirmation();
        $loginData = [
            'email' => $this->email,
            'password' => $this->password . '1'
        ];
        $loginInvalidPasswordResult = \ZXC\Modules\Auth\Auth::logIn($loginData);
        $this->assertNull($loginInvalidPasswordResult);
        $this->cleanUsersTableInTestAuth();
    }

    public function testLoginWithUndefinedEmailInDb()
    {
        $this->testRegistrationWithoutEmailConfirmation();
        $loginData = [
            'email' => $this->undefinedEmail,
            'password' => $this->password
        ];
        $loginInvalidPasswordResult = \ZXC\Modules\Auth\Auth::logIn($loginData);
        $this->assertNull($loginInvalidPasswordResult);
        $this->cleanUsersTableInTestAuth();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid email
     */
    public function testLoginWithInvalidEmail()
    {
        $this->testRegistrationWithoutEmailConfirmation();
        $loginData = [
            'email' => $this->invalidEmail,
            'password' => $this->password
        ];
        \ZXC\Modules\Auth\Auth::logIn($loginData);
    }

    public function testLoginWithoutEmailConformation()
    {
        $this->cleanUsersTableInTestAuth();
        $registrationData = [
            'login' => $this->login,
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];

        //register user
        $registrationOk = \ZXC\Modules\Auth\Auth::registration($registrationData);
        $this->assertTrue($registrationOk);

        $loginData = [
            'email' => $this->email,
            'password' => $this->password
        ];

        $blockedUserResult = \ZXC\Modules\Auth\Auth::logIn($loginData);
        $this->assertNull($blockedUserResult);
        $this->cleanUsersTableInTestAuth();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Login error, $data not found
     */
    public function testLoginWithoutData()
    {
        \ZXC\Modules\Auth\Auth::logIn();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Email field is required
     */
    public function testLoginWithoutEmail()
    {
        \ZXC\Modules\Auth\Auth::logIn(['password' => $this->password]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Password field is required
     */
    public function testLoginWithoutPassword()
    {
        \ZXC\Modules\Auth\Auth::logIn(['email' => $this->email]);
    }
    //TODO keep JWT in token table
    //TODO проверка токена при запросах

    public function testConfirmEmail()
    {
        $this->cleanUsersTableInTestAuth();
        $registrationData = [
            'login' => $this->login,
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];

        //register user
        $registrationOk = \ZXC\Modules\Auth\Auth::registration($registrationData);
        $this->assertTrue($registrationOk);

        $provider = \ZXC\Modules\Auth\Auth::getProvider();
        $structure = $provider->getStructure();
        $structure = $structure->withWhere(['email' => ['value' => $this->email]]);
        $select = $structure->select();
        $result = $provider->getDb()->exec($select, $structure->getValues());
        $this->assertSame(1, $result[0]['block_status']);
        $this->assertSame(0, $result[0]['email_activation']);
        $this->assertNotEmpty($result[0]['email_activation_code']);

        $confirmResult = \ZXC\Modules\Auth\Auth::confirmEmail([
            'email' => $this->email,
            'code' => $result[0]['email_activation_code']
        ]);

        $this->assertTrue($confirmResult);

        $structure = $provider->getStructure();
        $structure = $structure->withWhere(['email' => ['value' => $this->email]]);
        $select = $structure->select();
        $result = $provider->getDb()->exec($select, $structure->getValues());
        $this->assertEmpty($result[0]['email_activation_code']);
        $this->assertSame(0, $result[0]['block_status']);
        $this->assertSame(1, $result[0]['email_activation']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $dara argument must isset "email" and "code" fields
     */
    public function testConfirmEmailWithInvalidData()
    {
        $this->cleanUsersTableInTestAuth();
        $registrationData = [
            'login' => $this->login,
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];

        //register user
        $registrationOk = \ZXC\Modules\Auth\Auth::registration($registrationData);
        $this->assertTrue($registrationOk);

        $confirmResult = \ZXC\Modules\Auth\Auth::confirmEmail([
            'email' => $this->email,
            'code' => 'qweqweqkljwpopdifsdkfp'
        ]);
        $this->assertNull($confirmResult);

        $confirmResult = \ZXC\Modules\Auth\Auth::confirmEmail([
            'email' => $this->email . 'asqwe',
            'code' => 'qweqweqkljwpopdifsdkfp'
        ]);
        $this->assertNull($confirmResult);

        \ZXC\Modules\Auth\Auth::confirmEmail([]);
    }

    public function testRemindPassword()
    {
        $this->cleanUsersTableInTestAuth();
        $registrationData = [
            'login' => $this->login,
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];

        //register user
        $registrationOk = \ZXC\Modules\Auth\Auth::registration($registrationData);
        $this->assertTrue($registrationOk);
        $remindResultNull = \ZXC\Modules\Auth\Auth::remindPassword(['email' => $this->email]);
        $this->assertNull($remindResultNull);

        $provider = \ZXC\Modules\Auth\Auth::getProvider();
        $structure = $provider->getStructure();
        $structure = $structure->withWhere(['email' => ['value' => $this->email]]);
        $select = $structure->select();
        $result = $provider->getDb()->exec($select, $structure->getValues());
        $this->assertSame(1, $result[0]['block_status']);
        $this->assertSame(0, $result[0]['email_activation']);
        $this->assertNotEmpty($result[0]['email_activation_code']);

        $confirmResult = \ZXC\Modules\Auth\Auth::confirmEmail([
            'email' => $this->email,
            'code' => $result[0]['email_activation_code']
        ]);

        $this->assertTrue($confirmResult);

        $remindResultNull = \ZXC\Modules\Auth\Auth::remindPassword(['email' => $this->email]);
        $this->assertTrue($remindResultNull);

        $remindResultNull = \ZXC\Modules\Auth\Auth::remindPassword(['email' => $this->email]);
        $this->assertNull($remindResultNull);
        $this->cleanUsersTableInTestAuth();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $dara argument must isset "email" field
     */
    public function testRemindPasswordNotByEmail()
    {
        $this->cleanUsersTableInTestAuth();
        $registrationData = [
            'login' => $this->login,
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];

        //register user
        $registrationOk = \ZXC\Modules\Auth\Auth::registration($registrationData);
        $this->assertTrue($registrationOk);
        $remindResultNull = \ZXC\Modules\Auth\Auth::remindPassword(['email' => $this->email]);
        $this->assertNull($remindResultNull);

        $provider = \ZXC\Modules\Auth\Auth::getProvider();
        $structure = $provider->getStructure();
        $structure = $structure->withWhere(['email' => ['value' => $this->email]]);
        $select = $structure->select();
        $result = $provider->getDb()->exec($select, $structure->getValues());
        $this->assertSame(1, $result[0]['block_status']);
        $this->assertSame(0, $result[0]['email_activation']);
        $this->assertNotEmpty($result[0]['email_activation_code']);

        $confirmResult = \ZXC\Modules\Auth\Auth::confirmEmail([
            'email' => $this->email,
            'code' => $result[0]['email_activation_code']
        ]);
        $this->assertTrue($confirmResult);

        $config = $provider->getConfig();
        $config['reminder']['by'] = 'custom';
        $provider->initialize($config);

        $remindResult = \ZXC\Modules\Auth\Auth::remindPassword(['email' => $this->email]);
        $this->assertSame($this->email, $remindResult['email']);
        $this->assertTrue(isset($remindResult['key']));

        $config = $provider->getConfig();
        $config['reminder']['by'] = 'custom';
        $provider->initialize($config);

        $remindResultNull = \ZXC\Modules\Auth\Auth::remindPassword(['email' => $this->email]);
        $this->assertNull($remindResultNull);

        \ZXC\Modules\Auth\Auth::remindPassword(null);
    }

    public function testChangePassword()
    {
        $this->cleanUsersTableInTestAuth();
        $registrationData = [
            'login' => $this->login,
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'agreement' => true
        ];

        //register user
        $registrationOk = \ZXC\Modules\Auth\Auth::registration($registrationData);
        $this->assertTrue($registrationOk);
        $remindResultNull = \ZXC\Modules\Auth\Auth::remindPassword(['email' => $this->email]);
        $this->assertNull($remindResultNull);

        $provider = \ZXC\Modules\Auth\Auth::getProvider();
        $structure = $provider->getStructure();
        $structure = $structure->withWhere(['email' => ['value' => $this->email]]);
        $select = $structure->select();
        $result = $provider->getDb()->exec($select, $structure->getValues());
        $this->assertSame(1, $result[0]['block_status']);
        $this->assertSame(0, $result[0]['email_activation']);
        $this->assertNotEmpty($result[0]['email_activation_code']);

        $confirmResult = \ZXC\Modules\Auth\Auth::confirmEmail([
            'email' => $this->email,
            'code' => $result[0]['email_activation_code']
        ]);
        $this->assertTrue($confirmResult);

        $config = $provider->getConfig();
        $config['reminder']['by'] = 'custom';
        $provider->initialize($config);

        $remindResult = \ZXC\Modules\Auth\Auth::remindPassword(['email' => $this->email]);
        $this->assertSame($this->email, $remindResult['email']);
        $this->assertTrue(isset($remindResult['key']));

        $changePasswordData = [
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'key' => $remindResult['key'],
        ];

        $changeResult = \ZXC\Modules\Auth\Auth::changePassword(['email' => $this->email]);
        $this->assertNull($changeResult);

        $changeResult = \ZXC\Modules\Auth\Auth::changePassword($changePasswordData);
        $this->assertTrue($changeResult);

        $changePasswordData = [
            'email' => $this->email,
            'password' => $this->password,
            'passwordRepeat' => $this->password,
            'key' => $remindResult['key'] . "asdwe1",
        ];
        $changeResultNull = \ZXC\Modules\Auth\Auth::changePassword($changePasswordData);
        $this->assertNull($changeResultNull);
    }
}