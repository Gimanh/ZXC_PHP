<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 22/11/2018
 * Time: 22:02
 */

use \PHPUnit\Framework\TestCase;

class RoleOLDTest extends TestCase
{
    private $userId = 1;

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
                'value' => [1, 2, 3, 4]
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
                'value' => [1, 2, 3, 4]
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

    public function testFetchRolesByUserId()
    {
        $config = [
            'role' => [
                'structure' => [
                    'roles' => 'rolesTest',
                    'userRole' => 'userRoleTest'
                ],
                'db' => [
                    'instance' => 'inner',
                ],
            ],
        ];
        $this->createRoles();
        $this->addUserWithRoles();
        $role = new \ZXC\Modules\RBAC\RoleOLDOLD();
        $role->initialize($config['role']);

        $fetched = $role->fetchUserRolesById($this->userId);
        $this->assertTrue($fetched);

        $fetchedNull = $role->fetchUserRolesById(123);
        $this->assertNull($fetchedNull);

        $this->clearRoles();
        $this->clearUserWithRoles();
    }

    public function testHasRole()
    {
        $config = [
            'role' => [
                'structure' => [
                    'roles' => 'rolesTest',
                    'userRole' => 'userRoleTest'
                ],
                'db' => [
                    'instance' => 'inner',
                ],
            ],
        ];
        $this->createRoles();
        $this->addUserWithRoles();
        $role = new \ZXC\Modules\RBAC\RoleOLDOLD();
        $role->initialize($config['role']);

        $fetched = $role->fetchUserRolesById($this->userId);
        $this->assertTrue($fetched);

        $hasAdmin = $role->hasRole('admin');
        $this->assertTrue($hasAdmin);

        $hasModerator = $role->hasRole('moderator');
        $this->assertTrue($hasModerator);

        $hasUser = $role->hasRole('user');
        $this->assertTrue($hasUser);

        $hasGuest = $role->hasRole('guest');
        $this->assertTrue($hasGuest);

        $this->clearRoles();
        $this->clearUserWithRoles();
    }

    public function testGetRoleId()
    {
        $config = [
            'role' => [
                'structure' => [
                    'roles' => 'rolesTest',
                    'userRole' => 'userRoleTest'
                ],
                'db' => [
                    'instance' => 'inner',
                ],
            ],
        ];
        $this->createRoles();
        $this->addUserWithRoles();
        $role = new \ZXC\Modules\RBAC\RoleOLDOLD();
        $role->initialize($config['role']);

        $fetched = $role->fetchUserRolesById($this->userId);
        $this->assertTrue($fetched);

        $adminId = $role->getRoleId('admin');
        $this->assertSame(1, $adminId);

        $moderatorId = $role->getRoleId('moderator');
        $this->assertSame(2, $moderatorId);

        $userId = $role->getRoleId('user');
        $this->assertSame(3, $userId);

        $guestId = $role->getRoleId('guest');
        $this->assertSame(4, $guestId);

        $this->clearRoles();
        $this->clearUserWithRoles();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument $config is required
     */
    public function testInvalidConfig()
    {
        $role = new \ZXC\Modules\RBAC\RoleOLDOLD();
        $role->initialize([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid config for DB connection
     */
    public function testInvalidDbParams()
    {
        $config = [
            'role' => [
                'structure' => [
                    'roles' => 'rolesTest',
                    'userRole' => 'userRoleTest'
                ],
                'db' => [
                    'instance' => 'new',
                    'options' => []
                ],
            ],
        ];
        $role = new \ZXC\Modules\RBAC\RoleOLDOLD();
        $role->initialize($config['role']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidStructure()
    {
        $config = [
            'role' => [
                'structure' => [
                    'roles' => 'rolesTest1',
                    'userRole' => 'userRoleTest'
                ],
                'db' => [
                    'instance' => 'inner',
                ],
            ],
        ];
        $role = new \ZXC\Modules\RBAC\RoleOLDOLD();
        $role->initialize($config['role']);
    }
}