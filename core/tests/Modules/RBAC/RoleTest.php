<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.11.2018
 * Time: 11:06
 */

use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    private $adminRoleId = 5;
    private $moduratorRoleId = 6;
    private $userRoleId = 7;
    private $guestRoleId = 8;
    private $userId = 1;

    public function createPermissions()
    {
        $structureData = \ZXC\Modules\SQL\StructureControl::getStructureByName('permissionsTest');
        /**
         * @var $db \ZXC\Native\DB
         */
        $db = \ZXC\Native\ModulesManager::getModule('DB');
        $localStructure = \ZXC\Modules\SQL\Structure::create($db->getDbType(), $structureData);
        $localStructure = $localStructure->withInsert([
            'perm_desc' => [
                'value' => ['create', 'update', 'edit', 'delete', 'show']
            ],
            'perm_id' => [
                'value' => [1, 2, 3, 4, 5]
            ]
        ]);
        $sqlInsertRoles = $localStructure->insert();
        $permInsertResult = $db->exec($sqlInsertRoles, $localStructure->getValues());
        return $permInsertResult;
    }

    public function createRolePerm()
    {
        $structureData = \ZXC\Modules\SQL\StructureControl::getStructureByName('rolePermTest');
        /**
         * @var $db \ZXC\Native\DB
         */
        $db = \ZXC\Native\ModulesManager::getModule('DB');
        $localStructure = \ZXC\Modules\SQL\Structure::create($db->getDbType(), $structureData);
        $localStructure = $localStructure->withInsert([
            'role_id' => [
                'value' => [
                    $this->adminRoleId,
                    $this->adminRoleId,
                    $this->adminRoleId,
                    $this->adminRoleId,
                    $this->moduratorRoleId,
                    $this->userRoleId,
                    $this->guestRoleId,
                    $this->guestRoleId,
                ]
            ],
            'perm_id' => [
                'value' => [1, 2, 3, 4, 3, 3, 4, 5]
            ]
        ]);
        $sqlInsertRoles = $localStructure->insert();
        $permInsertResult = $db->exec($sqlInsertRoles, $localStructure->getValues());
        return $permInsertResult;
    }

    public function createRoles()
    {
        $structureData = \ZXC\Modules\SQL\StructureControl::getStructureByName('rolesTest');
        /**
         * @var $db \ZXC\Native\DB
         */
        $db = \ZXC\Native\ModulesManager::getModule('DB');
        $localStructure = \ZXC\Modules\SQL\Structure::create($db->getDbType(), $structureData);
        $localStructure = $localStructure->withInsert([
            'role_name' => [
                'value' => ['admin', 'moderator', 'user', 'guest']
            ],
            'role_id' => [
                'value' => [$this->adminRoleId, $this->moduratorRoleId, $this->userRoleId, $this->guestRoleId]
            ]
        ]);
        $sqlInsertRoles = $localStructure->insert();
        $roleInsertResult = $db->exec($sqlInsertRoles, $localStructure->getValues());
        return $roleInsertResult;
    }

    public function clearRoles()
    {
        $structureData = \ZXC\Modules\SQL\StructureControl::getStructureByName('rolesTest');
        /**
         * @var $db \ZXC\Native\DB
         */
        $db = \ZXC\Native\ModulesManager::getModule('DB');
        $localStructure = \ZXC\Modules\SQL\Structure::create($db->getDbType(), $structureData);
        $sqlInsertRoles = $localStructure->delete();
        $roleInsertResult = $db->exec($sqlInsertRoles);
        return $roleInsertResult;
    }

    public function clearRolePerm()
    {
        $structureData = \ZXC\Modules\SQL\StructureControl::getStructureByName('rolePermTest');
        /**
         * @var $db \ZXC\Native\DB
         */
        $db = \ZXC\Native\ModulesManager::getModule('DB');
        $localStructure = \ZXC\Modules\SQL\Structure::create($db->getDbType(), $structureData);
        $sqlInsertRoles = $localStructure->delete();
        $permInsertResult = $db->exec($sqlInsertRoles);
        return $permInsertResult;
    }

    public function clearPermissions()
    {
        $structureData = \ZXC\Modules\SQL\StructureControl::getStructureByName('permissions');
        /**
         * @var $db \ZXC\Native\DB
         */
        $db = \ZXC\Native\ModulesManager::getModule('DB');
        $localStructure = \ZXC\Modules\SQL\Structure::create($db->getDbType(), $structureData);
        $sqlInsertRoles = $localStructure->delete();
        $permInsertResult = $db->exec($sqlInsertRoles);
        return $permInsertResult;
    }

    public function addUserWithRoles()
    {
        $structureData = \ZXC\Modules\SQL\StructureControl::getStructureByName('userRoleTest');
        /**
         * @var $db \ZXC\Native\DB
         */
        $db = \ZXC\Native\ModulesManager::getModule('DB');
        $localStructure = \ZXC\Modules\SQL\Structure::create($db->getDbType(), $structureData);
        $localStructure = $localStructure->withInsert([
            'user_id' => [
                'value' => [$this->userId, $this->userId, $this->userId, $this->userId]
            ],
            'role_id' => [
                'value' => [$this->adminRoleId, $this->moduratorRoleId, $this->userRoleId, $this->guestRoleId]
            ]
        ]);
        $sqlInsertRoles = $localStructure->insert();
        $roleInsertResult = $db->exec($sqlInsertRoles, $localStructure->getValues());
        return $roleInsertResult;
    }

    public function clearUserWithRoles()
    {
        $structureData = \ZXC\Modules\SQL\StructureControl::getStructureByName('userRoleTest');
        /**
         * @var $db \ZXC\Native\DB
         */
        $db = \ZXC\Native\ModulesManager::getModule('DB');
        $localStructure = \ZXC\Modules\SQL\Structure::create($db->getDbType(), $structureData);
        $sqlInsertRoles = $localStructure->delete();
        $roleInsertResult = $db->exec($sqlInsertRoles);
        return $roleInsertResult;
    }

    public function clearAll()
    {
        $this->clearRolePerm();
        $this->clearPermissions();
        $this->clearRoles();
        $this->clearUserWithRoles();
    }

    public function setAllData()
    {
        $this->createPermissions();
        $this->createRolePerm();
        $this->createRoles();
        $this->addUserWithRoles();
    }

    public function testGetRolePermissions()
    {
        $this->setAllData();
        $config = [
            'role' => [
                'structure' => [
                    'permissions' => 'permissionsTest',
                    'rolePerm' => 'rolePermTest',
                    'roles' => 'rolesTest',
                    'userRoles' => 'userRoleTest'
                ],
                'db' => [
                    'instance' => 'inner',
                ],
            ],
        ];
        $role = new \ZXC\Modules\RBAC\Role();
        $role->initialize($config['role']);
        $adminRoles = $role->getRolePermissions($this->adminRoleId);
        $this->assertTrue($adminRoles->hasPermission('create'));
        $this->assertTrue($adminRoles->hasPermission('update'));
        $this->assertTrue($adminRoles->hasPermission('edit'));
        $this->assertTrue($adminRoles->hasPermission('delete'));
        $this->assertFalse($adminRoles->hasPermission('delete art'));

        $userRoles = $role->getRolePermissions($this->userRoleId);
        $this->assertTrue($userRoles->hasPermission('edit'));
        $this->assertFalse($userRoles->hasPermission('delete'));
        $this->clearAll();
    }

    public function testGetAvailableRoles()
    {
        $this->setAllData();
        $config = [
            'role' => [
                'structure' => [
                    'permissions' => 'permissionsTest',
                    'rolePerm' => 'rolePermTest',
                    'roles' => 'rolesTest',
                    'userRoles' => 'userRoleTest'
                ],
                'db' => [
                    'instance' => 'inner',
                ],
            ],
        ];
        $role = new \ZXC\Modules\RBAC\Role();
        $role->initialize($config['role']);
        $rolesCollection = $role->getAvailableRoles();
        $this->assertSame(4, $rolesCollection->count());
        $this->assertTrue($rolesCollection->hasRole('admin'));
        $this->assertTrue($rolesCollection->hasRole('moderator'));
        $this->assertTrue($rolesCollection->hasRole('user'));
        $this->assertTrue($rolesCollection->hasRole('guest'));
        $this->assertFalse($rolesCollection->hasRole('superuser'));
        $this->clearAll();
    }

    public function testAddUserRole()
    {
        $this->setAllData();
        $config = [
            'role' => [
                'structure' => [
                    'permissions' => 'permissionsTest',
                    'rolePerm' => 'rolePermTest',
                    'roles' => 'rolesTest',
                    'userRoles' => 'userRoleTest'
                ],
                'db' => [
                    'instance' => 'inner',
                ],
            ],
        ];
        $role = new \ZXC\Modules\RBAC\Role();
        $role->initialize($config['role']);
        $role->addUserRole(2, $this->adminRoleId);
        $roleCollection = $role->getUserRoles(2);
        $this->assertTrue($roleCollection->hasRole('admin'));
        $this->assertFalse($roleCollection->hasRole('user'));
        $this->assertTrue($roleCollection->hasPerm('edit'));
        $this->assertFalse($roleCollection->hasPerm('editapp'));
        $this->clearAll();
    }
}