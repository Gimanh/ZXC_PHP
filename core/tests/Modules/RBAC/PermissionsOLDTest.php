<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 22/11/2018
 * Time: 22:03
 */

use PHPUnit\Framework\TestCase;

class PermissionsOLDTest extends TestCase
{
    private $adminRoleId = 5;
    private $moduratorRoleId = 6;
    private $userRoleId = 7;
    private $guestRoleId = 8;
    private $userId = 1;

    public function createPermissions()
    {
        $structureData = \ZXC\Modules\SQL\StructureControl::getStructureByName('permissions');
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

    public function testFetchPermissionsByRoleId()
    {
        $this->createPermissions();
        $this->createRolePerm();
        $this->createRoles();
        $config = [
            'permissions' => [
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
        $permission = new \ZXC\Modules\RBAC\PermissionsOLDOLD();
        $permission->initialize($config['permissions']);

        $permissionsForRole = $permission->fetchPermissionsByRoleId($this->adminRoleId);
        $this->assertTrue($permissionsForRole);

        $this->clearRoles();
        $this->clearPermissions();
        $this->clearRolePerm();
    }

    public function testHasPermissions()
    {
        $this->createPermissions();
        $this->createRolePerm();
        $this->createRoles();
        $config = [
            'permissions' => [
                'structure' => [
                    'permissions' => 'permissions',
                    'rolePerm' => 'rolePermTest',
                    'roles' => 'rolesTest',
                    'userRoles' => 'userRoleTest'
                ],
                'db' => [
                    'instance' => 'inner',
                ],
            ],
        ];
        $permission = new \ZXC\Modules\RBAC\PermissionsOLDOLD();
        $permission->initialize($config['permissions']);

        $permissionsForRole = $permission->fetchPermissionsByRoleId($this->adminRoleId);
        $this->assertTrue($permissionsForRole);

        $hasEdit = $permission->hasPermission('edit');
        $this->assertTrue($hasEdit);

        $hasCreate = $permission->hasPermission('create');
        $this->assertTrue($hasCreate);

        $hasWrite = $permission->hasPermission('write');
        $this->assertFalse($hasWrite);

        $permissionsForModerator = new \ZXC\Modules\RBAC\PermissionsOLDOLD();
        $permissionsForModerator->initialize($config['permissions']);

        $result = $permissionsForModerator->fetchPermissionsByRoleId($this->moduratorRoleId);
        $this->assertTrue($result);

        $hasEdit = $permissionsForModerator->hasPermission('edit');
        $this->assertTrue($hasEdit);

        $hasEdit = $permissionsForModerator->hasPermission('create');
        $this->assertFalse($hasEdit);

        $permissionsForUser = new \ZXC\Modules\RBAC\PermissionsOLDOLD();
        $permissionsForUser->initialize($config['permissions']);
        $permissionsForUser->fetchPermissionsByRoleId($this->userRoleId);

        $hasDelete = $permissionsForUser->hasPermission('delete');
        $this->assertFalse($hasDelete);

        $hasEdit = $permissionsForUser->hasPermission('edit');
        $this->assertTrue($hasEdit);

        $this->clearRoles();
        $this->clearPermissions();
        $this->clearRolePerm();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument $config is required
     */
    public function testInvalidConfig()
    {
        $permission = new \ZXC\Modules\RBAC\PermissionsOLDOLD();
        $permission->initialize([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid config for DB connection
     */
    public function testInvalidDbParams()
    {
        $permission = new \ZXC\Modules\RBAC\PermissionsOLDOLD();
        $config = [
            'permissions' => [
                'structure' => [
                    'permissions' => 'permissions',
                    'rolePerm' => 'rolePermTest',
                    'roles' => 'rolesTest',
                    'userRoles' => 'userRoleTest'
                ],
                'db' => [
                    'instance' => 'new',
                    'options' => []
                ],
            ],
        ];

        $permission->initialize($config['permissions']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Can not load Structure permissionsTest1
     */
    public function testInvalidStructures()
    {
        $permission = new \ZXC\Modules\RBAC\PermissionsOLDOLD();
        $config = [
            'permissions' => [
                'structure' => [
                    'permissions' => 'permissionsTest1',
                    'rolePerm' => 'rolePermTest1',
                    'roles' => 'rolesTest',
                    'userRoles' => 'userRoleTest'
                ],
                'db' => [
                    'instance' => 'inner',
                ],
            ],
        ];

        $permission->initialize($config['permissions']);
    }

    public function testFetchPermissionsByUserId()
    {
        $this->createPermissions();
        $this->createRolePerm();
        $this->createRoles();
        $this->addUserWithRoles();
        $config = [
            'permissions' => [
                'structure' => [
                    'permissions' => 'permissions',
                    'rolePerm' => 'rolePermTest',
                    'roles' => 'rolesTest',
                    'userRoles' => 'userRoleTest'
                ],
                'db' => [
                    'instance' => 'inner',
                ],
            ],
        ];
        $permission = new \ZXC\Modules\RBAC\PermissionsOLDOLD();
        $permission->initialize($config['permissions']);
        $fetched = $permission->fetchPermissionsByUserId($this->userId);
        $this->assertTrue($fetched);

        $hasEdit = $permission->hasPermission('edit');
        $this->assertTrue($hasEdit);

        $hasCreate = $permission->hasPermission('create');
        $this->assertTrue($hasCreate);

        $hasWrite = $permission->hasPermission('write');
        $this->assertFalse($hasWrite);

        $hasShow = $permission->hasPermission('show');
        $this->assertTrue($hasShow);

        $this->clearPermissions();
        $this->clearRolePerm();
        $this->clearUserWithRoles();
        $this->clearRoles();
    }
}