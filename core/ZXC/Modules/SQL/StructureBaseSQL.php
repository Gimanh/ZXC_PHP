<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 18/08/2018
 * Time: 16:51
 */

namespace ZXC\Modules\SQL;


use ZXC\Interfaces\Modules\SQL\IStructureSQL;
use ZXC\Interfaces\Native\IStructure;

class StructureBaseSQL implements IStructure, IStructureSQL
{
    protected $name;
    protected $joinAlias;
    protected $fields;
    protected $table;
    protected $join;
    protected $where;
    protected $values;
    protected $update;
    protected $insert;

    public function __construct($structureParams)
    {
        $this->name = $structureParams['name'];
        $this->joinAlias = $structureParams['name'];
        $this->fields = $structureParams['fields'];
        $this->table = $structureParams['table'];
    }

    /**
     * @return string
     */
    public function getFieldsString()
    {
        $alias = $this->getJoinAlias();
        $fields = [];
        foreach ($this->fields as $field => $value) {
            $fields[] = $alias . '.' . $field;
        }

        if ($this->join) {
            foreach ($this->join as $f => $k) {
                /**
                 * @var $struct StructureBaseSQL
                 */
                $struct = $k['structure'];
                $fields[] .= $struct->getFieldsString();
            }
        }
        $sqlFields = implode(', ', $fields);
        $sqlFields .= ' ';
        return $sqlFields;
    }

    public function getFromString()
    {
        return 'FROM ' . $this->table . ' AS ' . $this->getJoinAlias() . ' ';

    }

    public function getJoinString()
    {
        $string = '';
        if (count($this->join) > 0) {
            foreach ($this->join as $join) {
                /**
                 * @var $structure StructureBaseSQL
                 */
                $structure = $join['structure'];
                $options = $join['options'];

                if (isset($options['mode']) && $options['mode']) {
                    $string .= $options['mode'] . ' JOIN ';
                } else {
                    $string .= 'LEFT JOIN ';
                }
                $string .= $structure->getTable() . ' ';

                $string .= 'AS ' . $structure->getJoinAlias() . ' ';
                if (!$options['fieldsCorrelation']) {
                    throw new \InvalidArgumentException('fieldsCorrelation for structure ' . $structure->getName() . ' undefined');
                }
                $i = 0;
                $length = count($options['fieldsCorrelation']);
                $string .= 'ON ';
                foreach ($options['fieldsCorrelation'] as $field => $value) {
                    $i++;
                    $string .= $structure->getJoinAlias() . '.' . $field . ' ';
                    if ($value['condition']) {
                        $string .= $value['condition'] . ' ';
                    } else {
                        $string .= '= ';
                    }
                    if (isset($value['tableAlias'])) {
                        $string .= $value['tableAlias'] . '.' . $value['field'] . ' ';
                    } else {
                        $string .= $value['field'] . ' ';
                    }
                    if ($i < $length) {
                        if ($value['operator']) {
                            $string .= $value['operator'] . ' ';
                        } else {
                            $string .= 'AND ';
                        }
                    }
                }
            }
            return $string . PHP_EOL;
        } else {
            return ' ';
        }
    }

    public function getWhereString()
    {
        return $this->buildWhereString();
    }

    public function getWhereFieldsString()
    {
        $length = count($this->where);
        $whereResult = [];
        if ($length > 0) {
            $i = 0;
            foreach ($this->where as $fieldName => $fieldValue) {
                $i++;
                $alias = $this->getJoinAlias();
                if (isset($fieldValue['conditions']) && $fieldValue['conditions']) {
                    if ($alias) {
                        $whereResult [] = $alias . '.' . $fieldName . $fieldValue['conditions'] . '? ';
                    } else {
                        $whereResult [] = $fieldName . $fieldValue['conditions'] . '? ';
                    }
                } else {
                    if ($alias) {
                        $whereResult [] = $alias . '.' . $fieldName . '= ' . '? ';
                    } else {
                        $whereResult [] = $fieldName . '= ' . '? ';
                    }
                }
                if (isset($fieldValue['operator']) && $fieldValue['operator'] && $i < $length) {
                    $whereResult [] = $fieldValue['operator'];
                } else {
                    if ($i < $length) {
                        $whereResult [] = 'AND ';
                    }
                }
                $this->values[] = $fieldValue['value'];
            }
            return implode(' ', $whereResult) . ' ';
        } else {
            return '';
        }
    }

    protected function buildWhereString()
    {
        $stringWhere = $this->getWhereFieldsString();
        if ($this->join) {
            foreach ($this->join as $join) {
                // KEY useNativeWhere  for joined structure if we set some where
                // for joined structure with this key we can use it
                if (isset($join['options']['useNativeWhere']) && $join['options']['useNativeWhere']) {
                    /**
                     * @var $structure StructureBaseSQL
                     */
                    $structure = $join['structure'];
                    if ($join['options']['joinOperatorWhere']) {
                        $stringWhere .= $join['options']['joinOperatorWhere'] . ' ' . $structure->getWhereFieldsString();
                    } else {
                        $stringWhere .= 'AND ' . $structure->getWhereFieldsString();
                    }
                    $this->values = array_merge($this->values, $structure->getValues());
                }
            }
        }
        if ($stringWhere) {
            return 'WHERE ' . $stringWhere . ' ';
        } else {
            return ' ';
        }
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @inheritdoc
     */
    public function join(StructureBaseSQL $structure, $joinOptions)
    {
        $this->join[] = ['structure' => $structure, 'options' => $joinOptions];
    }

    /**
     * @param StructureBaseSQL $structure
     * @param $joinOptions
     * @return StructureBaseSQL
     */
    public function withJoin(StructureBaseSQL $structure, $joinOptions)
    {
        $new = clone $this;
        $new->join[] = ['structure' => $structure, 'options' => $joinOptions];
        return $new;
    }

    /**
     * @param $where
     * @return $this
     */
    public function setWhere($where)
    {
        $this->where = $where;
        return $this;
    }

    /**
     * @param $where
     * @return StructureBaseSQL
     */
    public function withWhere($where)
    {
        $new = clone $this;
        $new->where = $where;
        return $new;
    }

    /**
     * @return mixed
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @inheritdoc
     */
    public function getString()
    {
        throw new \BadFunctionCallException("Override method " . __FUNCTION__ . ' in ' . get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function select()
    {
        throw new \BadFunctionCallException("Override method " . __FUNCTION__ . ' in ' . get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        throw new \BadFunctionCallException("Override method " . __FUNCTION__ . ' in ' . get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function update()
    {
        throw new \BadFunctionCallException("Override method " . __FUNCTION__ . ' in ' . get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function insert()
    {
        throw new \BadFunctionCallException("Override method " . __FUNCTION__ . ' in ' . get_called_class());
    }

    /**
     * @return string
     */
    public function getJoinAlias()
    {
        return $this->joinAlias;
    }

    /**
     * @param string $joinAlias
     */
    public function setJoinAlias($joinAlias)
    {
        $this->joinAlias = $joinAlias;
    }

    /**
     * @param $joinAlias
     * @return StructureBaseSQL
     */
    public function withJoinAlias($joinAlias)
    {
        $new = clone $this;
        $new->joinAlias = $joinAlias;
        return $new;
    }

    /**
     * @return mixed
     */
    public function getValues()
    {
        if (is_array($this->values[0]) && count($this->fields) === count($this->values)) {
            $copyValues = $this->values;
            $sub = array_fill(0, count($copyValues[0]), array_fill(0, count($this->fields), null));
            foreach ($copyValues as $k => $v) {
                foreach ($v as $c => $item) {
                    $sub[$c][$k] = $item;
                }
            }
            $flat = call_user_func_array('array_merge', $sub);
            return $flat;
        } else {
            if (is_array($this->values[0])) {
                return $this->values[0];
            }
        }
        return $this->values;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * @param mixed $update
     */
    public function setUpdate($update)
    {
        $this->update = $update;
    }

    /**
     * @param $update
     * @return StructureBaseSQL
     */
    public function withUpdate($update)
    {
        $new = clone $this;
        $new->update = $update;
        return $new;
    }

    /**
     * @return mixed
     */
    public function getInsert()
    {
        return $this->insert;
    }

    /**
     * @param mixed $insert
     */
    public function setInsert($insert)
    {
        $this->insert = $insert;
    }

    /**
     * @param $insert
     * @return StructureBaseSQL
     */
    public function withInsert($insert)
    {
        $new = clone $this;
        $new->insert = $insert;
        return $new;
    }

    public function getValue($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }
        return null;
    }
}