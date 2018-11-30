<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 06/09/2018
 * Time: 22:42
 */

namespace ZXC\Modules\RBAC;

use \ZXC\Interfaces\Modules\RBAC\IRoleOLD;
use ZXC\Modules\SQL\Structure;
use ZXC\Modules\SQL\StructureBaseSQL;
use ZXC\Modules\SQL\StructureControl;
use ZXC\Native\ModulesManager;

class RoleOLDOLD implements IRoleOLD, \ZXC\Interfaces\ZXC
{
    private $config = null;
    private $roles = [];
    /**
     * @var $rolesStructure StructureBaseSQL
     */
    private $rolesStructure = null;
    /**
     * @var $userRoleStructure StructureBaseSQL
     */
    private $userRoleStructure = null;
    /**
     * @var $db \ZXC\Interfaces\Native\DB
     */
    private $db = null;

    public function fetchUserRolesById($userId)
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
        $localStructure = $localStructure->withWhere([
            'user_id' => [
                'value' => $userId
            ]
        ]);
        $select = $localStructure->select();
        $result = $this->db->exec($select, $localStructure->getValues());
        if (!$result) {
            return null;
        }
        foreach ($result as $item => $value) {
            $this->roles[$value['role_name']] = $value['role_id'];
        }
        return true;
    }

    public function hasRole($roleName)
    {
        return array_key_exists($roleName, $this->roles);
    }

    /**
     * @param $roleName
     * @return int|null
     */
    public function getRoleId($roleName)
    {
        if (array_key_exists($roleName, $this->roles)) {
            return $this->roles[$roleName];
        }
        return null;
    }

    /**
     * Initialize class with config
     * @param array $config
     * @return bool
     */
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

        $this->rolesStructure = StructureControl::getStructureByName($this->config['structure']['roles']);
        $this->userRoleStructure = StructureControl::getStructureByName($this->config['structure']['userRole']);
        if (!$this->rolesStructure || !$this->userRoleStructure) {
            throw new \InvalidArgumentException('Can not load Structures ' .
                $this->config['structure']['roles'] . ' and ' . $this->config['structure']['userRole']);
        }
        $this->rolesStructure = Structure::create($this->db->getDbType(), $this->rolesStructure);
        $this->userRoleStructure = Structure::create($this->db->getDbType(), $this->userRoleStructure);
        if (!$this->rolesStructure) {
            throw new \InvalidArgumentException('Can not create role structure ');
        }
        if (!$this->userRoleStructure) {
            throw new \InvalidArgumentException('Can not create user role structure ');
        }


        return true;
    }
}