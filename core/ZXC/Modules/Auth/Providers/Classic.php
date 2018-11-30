<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 12/10/2018
 * Time: 23:55
 */

namespace ZXC\Modules\Auth\Providers;

use phpDocumentor\Reflection\Types\This;
use http\Exception\InvalidArgumentException;
use ZXC\Modules\Auth\Role;
use ZXC\Modules\Logger\Logger;
use ZXC\Native\Config;
use ZXC\Classes\Token;
use ZXC\Interfaces\Modules\Auth\Authentication;
use ZXC\Interfaces\Native\DB;
use ZXC\Modules\Mailer\Tx\Exceptions\CodeException;
use ZXC\Modules\Mailer\Tx\Exceptions\CryptoException;
use ZXC\Modules\Mailer\Tx\Exceptions\SMTPException;
use ZXC\Modules\SQL\Structure;
use ZXC\Modules\SQL\StructureBaseSQL;
use ZXC\Modules\SQL\StructureControl;
use ZXC\Native\Helper;
use ZXC\Native\HTTP\Request;
use ZXC\Native\I18N;
use ZXC\Modules\Mailer\Mail;
use ZXC\Native\ModulesManager;
use ZXC\ZXC;

class Classic implements Authentication
{
    /**
     * @var array
     */
    protected $config = null;
    /**
     * @var DB
     */
    protected $db = null;
    /**
     * @var StructureBaseSQL
     */
    protected $structure = null;
    /**
     * @var Mail
     */
    protected $smtp = null;
    /**
     * @var Logger
     */
    protected $logger = null;

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function logIn(array $data = null)
    {
        if (!$data) {
            throw new \InvalidArgumentException('Login error, $data not found');
        }

        if (!isset($data['email'])) {
            throw new \InvalidArgumentException('Email field is required');
        }

        if (!isset($data['password'])) {
            throw new \InvalidArgumentException('Password field is required');
        }

        $structureData = Helper::getConvertedArrayForStructureByKeys($data, [
            'email'
        ]);

        if (!Helper::isEmail($data['email'])) {
            throw new \InvalidArgumentException('Invalid email');
        }

        $structureData['email']['value'] = Helper::getCleanEmail($structureData['email']['value']);

        $localStructure = $this->structure->withWhere($structureData);
        $selectUser = $localStructure->select();
        $result = $this->db->exec($selectUser, $localStructure->getValues());

        if (!$result) {
            return null;
        }

        if (!Helper::passwordVerify($data['password'], $result[0]['password'])) {
            return null;
        }

        if (intval($result[0]['block_status']) === 1) {
            return null;
        }
        $exp = isset($this->config['token']['access']['expire']) ? $this->config['token']['access']['expire'] : 15;

        $payload = [
            'id' => $result[0]['id'],
            'email' => $result[0]['email'],
            'exp' => time() + ($exp * 60)
        ];

        $aud = isset($this->config['token']['access']['aud']) ? $this->config['token']['access']['aud'] : null;
        if ($aud) {
            $payload['aud'] = $aud;
        }

        $alg = $this->config['token']['alg'];
        $secret = $this->config['token']['secret_key'];
        $token = Token::encode($payload, $secret, $alg);
        return [
            'token' => $token,
            'id' => $result[0]['id'],
            'login' => $result[0]['login'],
            'email' => $result[0]['email']
        ];
    }

    /**
     * @inheritdoc
     */
    public function logOut(array $data = null)
    {
        // TODO: Implement logOut() method.
    }

    public function logOutFromAllAccounts(array $data = null)
    {
        // TODO: Implement logOut() method.
    }

    /**
     * Check JWT
     */
    public function check()
    {
        // TODO: Implement logOut() method.
    }

    /**
     * @inheritdoc
     */
    public function registration(array $data = null)
    {
        if (!$data) {
            throw new \InvalidArgumentException('$data argument must isset next fields "mail", "password", "passwordRepeat", "agreement"');
        }

        if ($data['password'] !== $data['passwordRepeat']) {
            throw new \InvalidArgumentException('Passwords does not equal');
        }

        if (!isset($data['agreement']) || (bool)$data['agreement'] !== true) {
            throw new \InvalidArgumentException('Field agreement must be true');
        }
        $data['agreement'] = 1;

        $postData = Helper::getConvertedArrayForStructureByKeys($data, [
            'email',
            'login',
            'password',
            'agreement'
        ]);

        if (!Helper::isEmail($data['email'])) {
            throw new \InvalidArgumentException('Invalid email');
        }
        if (!Helper::isValidLogin($data['login'])) {
            throw new \InvalidArgumentException('Login must isset only character and have length between 4-20');
        }
        $postData['email']['value'] = Helper::getCleanEmail($data['email']);

        if (isset($this->config['strongPassword']['value'])) {
            if ($this->config['strongPassword']['value'] === true) {
                if (isset($this->config['strongPassword']['callback'])) {
                    $callback = $this->config['strongPassword']['callback'];
                    $validPassword = Helper::callCallback($callback, $data['password']);
                    if (!$validPassword) {
                        throw new \InvalidArgumentException('Password is not strong');
                    }
                } else {
                    if (!Helper::isValidStrongPassword($data['password'])) {
                        throw new \InvalidArgumentException('Password is not strong');
                    }
                }
            }
        }

        if (isset($this->config['confirmEmail']) && $this->config['confirmEmail'] === true) {
            $postData['email_activation_code'] = ['value' => Helper::createHash()];
        } else {
            $postData['block_status'] = ['value' => 0];
        }

        $postData['password'] = ['value' => Helper::getPasswordHash($data['password'])];

        $localStructure = $this->structure->withInsert($postData);
        $sqlInsert = $localStructure->insert();
        $result = $this->db->exec($sqlInsert, $localStructure->getValues());

        if (!$result) {
            return null;
        }
        if (isset($this->config['confirmEmail']) && $this->config['confirmEmail'] === true) {
            $confirmUrl = $this->config['uri']['confirm'];
            $lastSlash = substr($confirmUrl, -1);
            if ($lastSlash === '/') {
                $confirmUrl = rtrim($confirmUrl, '/');
            }
            $confirmUrl .= '/email/' . $postData['email']['value'] . '/code/' . $postData['email_activation_code']['value'];
            if ($this->smtp && $result) {
                try {
                    $sendingResult = $this->smtp
                        ->addTo('User', $data['email'])
                        ->setSubject(I18N::t('Email confirmation'))
                        ->setBody("<a href='$confirmUrl'>" .
                            I18N::t('To confirm registration use follow the link') .
                            "</a>")
                        ->send();
                    if (!$sendingResult) {
                        $this->logger->error('Can not send email for user ',
                            ['email' => $data['email'], 'login' => $data['login']]);
                    }
                } catch (CodeException $e) {
                    $this->logger && $this->logger->error($e->getMessage(),
                        ['email' => $data['email'], 'login' => $data['login']]);
                } catch (CryptoException $e) {
                    $this->logger && $this->logger->error($e->getMessage(),
                        ['email' => $data['email'], 'login' => $data['login']]);
                } catch (SMTPException $e) {
                    $this->logger && $this->logger->error($e->getMessage(),
                        ['email' => $data['email'], 'login' => $data['login']]);
                }
            }
        }
        return (bool)$result;
    }

    /**
     * @inheritdoc
     */
    public function confirmEmail(array $data = null)
    {
        if (!$data) {
            throw new \InvalidArgumentException('$dara argument must isset "email" and "code" fields');
        }
        $postData = Helper::getConvertedArrayForStructureByKeys($data, ['email', 'code']);
        $postData['email_activation_code'] = $postData['code'];
        unset($postData['code']);
        $localStructure = $this->structure->withWhere($postData);
        $sql = $localStructure->select();
        $selectResult = $this->db->exec($sql, $localStructure->getValues());
        if (!$selectResult) {
            $this->logger->error('Can not select record with activation code for user', $postData);
            return null;
        }

        if ($selectResult[0]['email_activation_code'] !== $postData['email_activation_code']['value']) {
            return null;
        }

        $updateLocalStructure = $this->structure->withUpdate([
            'email_activation_code' => ['value' => ''],
            'block_status' => ['value' => 0],
            'email_activation' => ['value' => 1]
        ]);
        $updateSql = $updateLocalStructure->update();
        $updateResult = $this->db->exec($updateSql, $updateLocalStructure->getValues());

        if (!$updateResult) {
            $this->logger->error('Can not update activation code for user', $postData);
            return null;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function remindPassword(array $data = null)
    {
        if (!$data) {
            throw new \InvalidArgumentException('$dara argument must isset "email" field');
        }
        if (!$data || !isset($data['email']) || !Helper::isEmail($data['email'])) {
            return null;
        }
        $postData = Helper::getConvertedArrayForStructureByKeys($data, [
            'email'
        ]);
        if (!$postData) {
            return null;
        }
        $postData['email']['value'] = Helper::getCleanEmail($data['email']);
        $localStructure = $this->structure->withWhere($postData);
        $select = $localStructure->select();
        $result = $this->db->exec($select, $localStructure->getValues());

        if ($result[0]['block_status'] == 1) {
            return null;
        }
        if ($result[0]['last_remind_time']) {
            $timePassed = time() - $result[0]['last_remind_time'];
            if ($timePassed < $this->config['reminder']['frequency']) {
                $this->logger && $this->logger->warning('Reminder time passed error ',
                    ['email' => $postData['email']['value']]);
                return null;
            }
        }

        if (!$result) {
            return null;
        }

        $hash = Helper::createHash();
        $localStructureUpdate = $this->structure->withUpdate([
            'remind_password_code' => ['value' => $hash],
            'last_remind_time' => ['value' => time()]
        ]);
        $localStructureUpdate = $localStructureUpdate->withWhere($postData);
        $updateQuery = $localStructureUpdate->update();
        $updateResult = $this->db->exec($updateQuery, $localStructureUpdate->getValues());
        if (!$updateResult) {
            return null;
        }

        if (isset($this->config['reminder']['by']) && $this->config['reminder']['by'] === 'email') {
            $url = $this->config['uri']['reminder'] . '/email/' . $result[0]['email'] . '/key/' . $hash;
            if ($this->smtp) {
                try {
                    $this->smtp
                        ->addTo('User', $data['email'])
                        ->setSubject(I18N::t('Reset password link'))
                        ->setBody("<a href='$url'>" .
                            I18N::t('Reset password link') .
                            "</a>")
                        ->send();
                } catch (CodeException $e) {
                    $this->logger && $this->logger->error($e->getMessage(),
                        ['email' => $data['email'], 'login' => $data['login']]);
                    return null;
                } catch (CryptoException $e) {
                    $this->logger && $this->logger->error($e->getMessage(),
                        ['email' => $data['email'], 'login' => $data['login']]);
                    return null;
                } catch (SMTPException $e) {
                    $this->logger && $this->logger->error($e->getMessage(),
                        ['email' => $data['email'], 'login' => $data['login']]);
                    return null;
                }
            }
        } else {
            return ['email' => $result[0]['email'], 'key' => $hash];
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function changePassword(array $data = null)
    {
        if (!$data) {
            throw new InvalidArgumentException('$dara argument must isset "email", "password", "passwordRepeat","key" fields');
        }
        if (!Helper::issetKeys($data, ['password', 'passwordRepeat', 'email', 'key'])) {
            return null;
        }
        if ($data['password'] !== $data['passwordRepeat']) {
            return null;
        }
        $postData = Helper::getConvertedArrayForStructureByKeys($data, [
            'email',
            'key'
        ]);
        $postData['remind_password_code'] = $postData['key'];
        unset($postData['key']);

        $localStructure = $this->structure->withWhere($postData);
        $select = $localStructure->select();
        $result = $this->db->exec($select, $localStructure->getValues());
        if (!$result) {
            return null;
        }
        $newPasswordHash = Helper::getPasswordHash($data['password']);
        $localStructureUpdate = $this->structure->withUpdate([
            'password' => ['value' => $newPasswordHash],
            'remind_password_code' => ['value' => ''],
            'last_remind_time' => ['value' => 0],
        ]);
        $localStructureUpdate = $localStructureUpdate->withWhere($postData);
        $updateSql = $localStructureUpdate->update();
        $updateResult = $this->db->exec($updateSql, $localStructureUpdate->getValues());
        if (!$updateResult) {
            $this->logger && $this->logger->warning('Update password error ', ['email' => $postData['login']['value']]);
            return null;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function initialize(array $config = null)
    {
        if (!$config) {
            throw new \InvalidArgumentException('Config is undefined');
        }

        $this->config = $config;

        if (isset($this->config['db']['instance'])) {
            if ($this->config['db']['instance'] === 'new') {
                $this->db = ModulesManager::getNewModule('DB', $this->config['db']['options']);
            } else {
                $this->db = ModulesManager::getModule('DB');
            }
        } else {
            $this->db = ModulesManager::getModule('DB');
        }

        if (!$this->db) {
            throw new \InvalidArgumentException('Module "DB" not found in ZXC/Modules');
        }

        $authStructure = StructureControl::getStructureByName($this->config['structure']);
        if (!$authStructure) {
            throw new \InvalidArgumentException('Structure ' . $this->config['structure'] . ' not found');
        }

        $this->structure = Structure::create($this->db->getDbType(), $authStructure);

        if ($this->config['confirmEmail']) {
            if (isset($this->config['mailer']['instance'])) {
                if ($this->config['mailer']['instance'] === 'new') {
                    if (!isset($this->config['mailer']['options'])) {
                        throw new \InvalidArgumentException('Options undefined in config file set them in ZXC/Modules/Auth/mailer');
                    }
                    $this->smtp = ModulesManager::getNewModule('Mailer', $this->config['mailer']['options']);
                } else {
                    $this->smtp = ModulesManager::getModule('Mailer');
                }
            } else {
                $this->smtp = ModulesManager::getModule('Mailer');
            }

            if (!$this->smtp) {
                throw new \InvalidArgumentException('Module "Mailer" not found in ZXC/Modules');
            }
        }

        if (isset($this->config['logger'])) {
            if (isset($this->config['logger']['value']) && $this->config['logger']['value'] === true) {
                if (isset($this->config['logger']['instance']) && $this->config['logger']['instance'] === 'new') {
                    if (!isset($this->config['logger']['options'])) {
                        throw new \InvalidArgumentException('Options for new Logger instance undefined in ZXC/Modules/Auth/options/logger');
                    }
                    $this->logger = ModulesManager::getNewModule('Logger', $this->config['logger']['options']);
                } else {
                    $this->logger = ModulesManager::getModule('Logger');
                }
                if (!$this->logger) {
                    throw new \InvalidArgumentException('Can not create logger instance');
                }
            }
        }
        return true;
    }

    /**
     * @return StructureBaseSQL
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @return DB
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getLogger()
    {
        return $this->logger;
    }
}