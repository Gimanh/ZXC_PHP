<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.11.2018
 * Time: 9:49
 */

namespace ZXC\Modules\RBAC;

use ZXC\Interfaces\Native\DB;
use ZXC\Modules\SQL\Structure;
use ZXC\Modules\SQL\StructureBaseSQL;
use ZXC\Modules\SQL\StructureControl;
use ZXC\Native\ModulesManager;

class Role implements IRole
{
    /**
     * @var DB
     */
    protected $db = null;
    /**
     * @var null|string
     */
    protected $roleName = null;
    /**
     * @var array
     */
    protected $config = [];
    /**
     * @var array
     */
    protected $permissions = [];
    /**
     * @var StructureBaseSQL
     */
    protected $rolePermStructure = null;
    /**
     * @var StructureBaseSQL
     */
    protected $permissionsStructure = null;
    /**
     * @var StructureBaseSQL
     */
    protected $rolesStructure = null;
    /**
     * @var StructureBaseSQL
     */
    protected $userRoleStructure = null;

    /**
     * @param int $roleId
     * @return IRole
     */
    public function getRolePermissions($roleId)
    {
        if (!$roleId) {
            throw new \InvalidArgumentException('Role id is required');
        }
        $role = new Role();
        $role->initialize($this->config);

        $join = [
            'mode' => 'LEFT',
            'fieldsCorrelation' => [
                'perm_id' => [
                    'condition' => '=',
                    'tableAlias' => $this->rolePermStructure->getJoinAlias(),
                    'field' => 'perm_id',
                ]
            ]
        ];
        $localStructure = $this->rolePermStructure->withJoin($this->permissionsStructure, $join);
        $join = [
            'mode' => 'LEFT',
            'fieldsCorrelation' => [
                'role_id' => [
                    'condition' => '=',
                    'tableAlias' => $localStructure->getJoinAlias(),
                    'field' => 'role_id',
                ]
            ]
        ];
        $localStructure = $localStructure->withJoin($this->rolesStructure, $join);
        $localStructure = $localStructure->withWhere([
            'role_id' => [
                'value' => $roleId
            ]
        ]);
        $select = $localStructure->select();
        $result = $this->db->exec($select, $localStructure->getValues());
        if (!$result) {
            return null;
        }
        $role->roleName = $result[0]['role_name'];
        foreach ($result as $item => $value) {
            $role->permissions[$value['perm_desc']] = $value['perm_id'];
        }
        return $role;
    }

    public function hasPermission($permissionName)
    {
        return isset($this->permissions[$permissionName]);
    }

    /**
     * @return null|RolesCollection
     */
    public function getAvailableRoles()
    {
        $select = $this->rolesStructure->select();
        $roles = $this->db->exec($select);
        if ($roles) {
            return $this->dbResultToRolesCollection($roles);
        }
        return null;
    }

    public function addUserRole($userId, $roleId)
    {
        $localStructure = $this->userRoleStructure->withInsert([
            'user_id' => [
                'value' => $userId
            ],
            'role_id' => [
                'value' => $roleId
            ]
        ]);
        $insert = $localStructure->insert();
        $result = $this->db->exec($insert, $localStructure->getValues());
        return $result;
    }

    public function getUserRoles($userId)
    {
        $localStructure = $this->userRoleStructure->withWhere([
            'user_id' => [
                'value' => $userId
            ]
        ]);
        $select = $localStructure->select();
        $roles = $this->db->exec($select, $localStructure->getValues());
        return $this->dbResultToRolesCollection($roles);
    }

    public function initialize(array $config = null)
    {
        if (!$config) {
            throw new \InvalidArgumentException('Argument $config is required');
        }
        $this->config = $config;
        if ($this->config['db']['instance'] === 'new') {
            $this->db = ModulesManager::getNewModule('DB', $this->config['db']['options']);
            if (!$this->db) {
                throw new \InvalidArgumentException('Can not get new module DB');
            }
        } else {
            $this->db = ModulesManager::getModule('DB');
            if (!$this->db) {
                throw new \InvalidArgumentException('Can not get module DB');
            }
        }

        $this->rolePermStructure = StructureControl::getStructureByName($this->config['structure']['rolePerm']);
        $this->permissionsStructure = StructureControl::getStructureByName($this->config['structure']['permissions']);
        $this->rolesStructure = StructureControl::getStructureByName($this->config['structure']['roles']);
        $this->userRoleStructure = StructureControl::getStructureByName($this->config['structure']['userRoles']);
        if (!$this->permissionsStructure) {
            throw new \InvalidArgumentException('Can not load Structure ' . $this->config['structure']['permissions']);
        }
        if (!$this->rolePermStructure) {
            throw new \InvalidArgumentException('Can not load Structure ' . $this->config['structure']['rolePerm']);
        }
        if (!$this->rolesStructure) {
            throw new \InvalidArgumentException('Can not load Structure ' . $this->config['structure']['roles']);
        }
        if (!$this->userRoleStructure) {
            throw new \InvalidArgumentException('Can not load Structure ' . $this->config['structure']['userRoles']);
        }
        $this->rolePermStructure = Structure::create($this->db->getDbType(), $this->rolePermStructure);
        $this->permissionsStructure = Structure::create($this->db->getDbType(), $this->permissionsStructure);
        $this->rolesStructure = Structure::create($this->db->getDbType(), $this->rolesStructure);
        $this->userRoleStructure = Structure::create($this->db->getDbType(), $this->userRoleStructure);
        if (!$this->permissionsStructure) {
            throw new \InvalidArgumentException('Can not create permissions structure ');
        }
        if (!$this->rolePermStructure) {
            throw new \InvalidArgumentException('Can not create rolePerm structure ');
        }
        if (!$this->rolesStructure) {
            throw new \InvalidArgumentException('Can not create roles structure ');
        }
        if (!$this->userRoleStructure) {
            throw new \InvalidArgumentException('Can not create userRoles structure ');
        }
        return true;
    }

    /**
     * @return null|string
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * @param $dbResult = [['role_id'=>7]...]
     * @return null|RolesCollection
     */
    public function dbResultToRolesCollection($dbResult)
    {
        if (!$dbResult) {
            return null;
        }
        $rolesInstances = [];
        foreach ($dbResult as $role => $value) {
            $roleInstance = $this->getRolePermissions($value['role_id']);
            $rolesInstances[] = $roleInstance;
        }
        return new RolesCollection($rolesInstances);
    }
}