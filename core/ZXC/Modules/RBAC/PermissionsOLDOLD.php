<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 30/10/2018
 * Time: 22:41
 */

namespace ZXC\Modules\RBAC;


use ZXC\Interfaces\Modules\RBAC\IPermissionsOLD;
use ZXC\Interfaces\Native\DB;
use ZXC\Interfaces\ZXC;
use ZXC\Modules\SQL\Structure;
use ZXC\Modules\SQL\StructureBaseSQL;
use ZXC\Modules\SQL\StructureControl;
use ZXC\Native\ModulesManager;

class PermissionsOLDOLD implements IPermissionsOLD, ZXC
{
    /**
     * @var DB
     */
    protected $db = null;
    private $config = [];
    private $permissions = [];
    private $roles = [];
    /**
     * @var StructureBaseSQL
     */
    private $rolePermStructure = null;
    /**
     * @var StructureBaseSQL
     */
    private $permissionsStructure = null;
    /**
     * @var StructureBaseSQL
     */
    private $rolesStructure = null;
    /**
     * @var StructureBaseSQL
     */
    private $userRoleStructure = null;

    public function fetchPermissionsByRoleId($roleId)
    {
        if (!$roleId) {
            throw new \InvalidArgumentException('Role id is required');
        }
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
        foreach ($result as $item => $value) {
            $this->permissions[$value['perm_desc']] = $value['perm_id'];
            $this->roles[$value['role_name']] = $value['role_id'];
        }
        return true;
    }

    public function hasPermission($permissionName)
    {
        return array_key_exists($permissionName, $this->permissions);
    }

    public function fetchPermissionsByUserId($userId)
    {
        $join = [
            'mode' => 'LEFT',
            'fieldsCorrelation' => [
                'role_id' => [
                    'condition' => '=',
                    'tableAlias' => $this->userRoleStructure->getJoinAlias(),
                    'field' => 'role_id',
                ]
            ]
        ];
        $localStructure = $this->userRoleStructure->withJoin($this->rolesStructure, $join);

        $join = [
            'mode' => 'LEFT',
            'fieldsCorrelation' => [
                'role_id' => [
                    'condition' => '=',
                    'tableAlias' => $this->rolesStructure->getJoinAlias(),
                    'field' => 'role_id',
                ]
            ]
        ];
        $localStructure = $localStructure->withJoin($this->rolePermStructure, $join);

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
        $localStructure = $localStructure->withJoin($this->permissionsStructure, $join);
        $localStructure = $localStructure->withWhere(['user_id' => ['value' => $userId]]);
        $query = $localStructure->select();
        $result = $this->db->exec($query, $localStructure->getValues());
        if (!$result) {
            return null;
        }
        foreach ($result as $item => $value) {
            $this->permissions[$value['perm_desc']] = $value['perm_id'];
            $this->roles[$value['role_name']] = $value['role_id'];
        }
        return true;
    }

    public function getAvailableRoles()
    {
        $select = $this->rolesStructure->select();
        $roles = $this->db->exec($select);
        return $roles;
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
}