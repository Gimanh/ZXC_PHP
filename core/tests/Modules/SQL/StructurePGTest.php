<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 19/08/2018
 * Time: 22:07
 */

use PHPUnit\Framework\TestCase;

class StructurePGTest extends TestCase
{
    private $structureParamsUsers;
    private $structureParamsArticles;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->structureParamsUsers = [
            'name' => 'userStructure',
            'table' => 'ptest.users',
            'fields' => [
                'name' => [
                    'type' => 'string'
                ],
                'login' => [
                    'type' => 'string'
                ],
                'password' => [
                    'type' => 'string'
                ],
                'status' => [
                    'type' => 'integer'
                ]
            ]
        ];
        $this->structureParamsArticles = [
            'name' => 'articlesStructure',
            'table' => 'ptest.articles',
            'fields' => [
                'id' => [
                    'type' => 'integer'
                ],
                'user_id' => [
                    'type' => 'integer'
                ],
                'text' => [
                    'type' => 'string'
                ]
            ]
        ];
        parent::__construct($name, $data, $dataName);
    }

    public function testSimpleSelect()
    {
        $structure = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $sqlString = $structure->select();
        $sqlString = preg_replace('!\s+!', ' ', $sqlString);
        $this->assertSame("SELECT userStructure.name, userStructure.login, userStructure.password, userStructure.status FROM ptest.users AS userStructure ",
            $sqlString);

    }

    public function testSimpleSelectWithWhere()
    {
        $structure = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $where = [
            'login' => [
                'value' => 'testlogin1',
                'operator' => 'AND'
            ],
            'password' => [
                'value' => '12345'
            ]
        ];
        $structure->setWhere($where);
        $sqlString = $structure->select();
        $sqlString = preg_replace('!\s+!', ' ', $sqlString);
        $values = $structure->getValues();
        $this->assertSame("SELECT userStructure.name, userStructure.login, userStructure.password, userStructure.status FROM ptest.users AS userStructure WHERE userStructure.login= ? AND userStructure.password= ? ",
            $sqlString);
        $this->assertSame(["testlogin1", "12345"], $values);
    }

    public function testSimpleSelectWithJoinAnotherStructure()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $structureArticles = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsArticles);

        $structureUsers->setJoinAlias("userAlias");
        $structureArticles->setJoinAlias('articlesAlias');

        $joinOptions = [
            'mode' => 'LEFT',
            'as' => 'articles',
            'joinOperatorWhere' => 'AND',
            'useNativeWhere' => false,
            'fieldsCorrelation' => [
                'user_id' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'id',
                    'operator' => 'AND'
                ]
            ]
        ];
        $where = [
            'login' => [
                'value' => 'testlogin1',
                'operator' => 'AND'
            ],
            'password' => [
                'value' => '12345'
            ]
        ];

        $structureUsers->join($structureArticles, $joinOptions);
        $structureUsers->setWhere($where);

        $sqlString = $structureUsers->select();
        $sqlString = trim(preg_replace('!\s+!', ' ', $sqlString));
        $values = $structureUsers->getValues();
        $this->assertSame(trim("SELECT userAlias.name, userAlias.login, userAlias.password, userAlias.status, articlesAlias.id, articlesAlias.user_id, articlesAlias.text FROM ptest.users AS userAlias LEFT JOIN ptest.articles AS articlesAlias ON articlesAlias.user_id = userAlias.id WHERE userAlias.login= ? AND userAlias.password= ? "),
            $sqlString);
        $this->assertSame(["testlogin1", "12345"], $values);
    }

    public function testSimpleSelectWithJoinAnotherStructureWithManyFieldsCorrelation()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $structureArticles = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsArticles);

        $structureUsers->setJoinAlias("userAlias");
        $structureArticles->setJoinAlias('articlesAlias');

        $joinOptions = [
            'mode' => 'LEFT',
            'as' => 'articles',
            'joinOperatorWhere' => 'AND',
            'useNativeWhere' => false,
            'fieldsCorrelation' => [
                'user_id' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'id',
                    'operator' => 'AND'
                ],
                'ident' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'name',
                    'operator' => 'AND'
                ]
            ]
        ];

        $where = [
            'login' => [
                'value' => 'testlogin1',
                'operator' => 'AND'
            ],
            'password' => [
                'value' => '12345'
            ]
        ];

        $structureUsers->join($structureArticles, $joinOptions);
        $structureUsers->setWhere($where);

        $sqlString = $structureUsers->select();
        $sqlString = trim(preg_replace('!\s+!', ' ', $sqlString));
        $values = $structureUsers->getValues();
        $this->assertSame(trim("SELECT userAlias.name, userAlias.login, userAlias.password, userAlias.status, articlesAlias.id, articlesAlias.user_id, articlesAlias.text FROM ptest.users AS userAlias LEFT JOIN ptest.articles AS articlesAlias ON articlesAlias.user_id = userAlias.id AND articlesAlias.ident = userAlias.name WHERE userAlias.login= ? AND userAlias.password= ? "),
            $sqlString);
        $this->assertSame(["testlogin1", "12345"], $values);
    }

    public function testSimpleSelectWithJoinAnotherStructureWithUseNativeWhere()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $structureArticles = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsArticles);

        $structureUsers->setJoinAlias("userAlias");
        $structureArticles->setJoinAlias('articlesAlias');

        $joinOptions = [
            'mode' => 'LEFT',
            'joinOperatorWhere' => 'AND',
            'useNativeWhere' => true,
            'fieldsCorrelation' => [
                'user_id' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'id',
                    'operator' => 'AND'
                ],
                'ident' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'name',
                    'operator' => 'AND'
                ]
            ]
        ];

        $where = [
            'login' => [
                'value' => 'testlogin1',
                'operator' => 'AND'
            ],
            'password' => [
                'value' => '12345'
            ]
        ];

        $whereArticle = [
            'text' => [
                'value' => 'text1',
                'operator' => 'AND'
            ],
            'ident' => [
                'value' => '1'
            ]
        ];
        $structureUsers->join($structureArticles, $joinOptions);
        $structureUsers->setWhere($where);
        $structureArticles->setWhere($whereArticle);
        $sqlString = $structureUsers->select();
        $sqlString = trim(preg_replace('!\s+!', ' ', $sqlString));
        $values = $structureUsers->getValues();
        $this->assertSame(trim("SELECT userAlias.name, userAlias.login, userAlias.password, userAlias.status, articlesAlias.id, articlesAlias.user_id, articlesAlias.text FROM ptest.users AS userAlias LEFT JOIN ptest.articles AS articlesAlias ON articlesAlias.user_id = userAlias.id AND articlesAlias.ident = userAlias.name WHERE userAlias.login= ? AND userAlias.password= ? AND articlesAlias.text= ? AND articlesAlias.ident= ?"),
            $sqlString);
        $this->assertSame(['testlogin1', '12345', 'text1', '1'], $values);
    }

    /**
     * DELETE SECTION
     */
    public function testSimpleDelete()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $sqlString = $structureUsers->delete();
        $sqlString = preg_replace('!\s+!', ' ', $sqlString);
        $this->assertSame('DELETE FROM ptest.users AS userStructure ', $sqlString);
    }

    public function testSimpleDeleteWithWhere()
    {
        $structure = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $where = [
            'login' => [
                'value' => 'testlogin1',
                'operator' => 'AND'
            ],
            'password' => [
                'value' => '12345'
            ]
        ];
        $structure->setWhere($where);
        $sqlString = $structure->delete();
        $sqlString = preg_replace('!\s+!', ' ', $sqlString);
        $values = $structure->getValues();
        $this->assertSame("DELETE FROM ptest.users AS userStructure WHERE userStructure.login= ? AND userStructure.password= ? ",
            $sqlString);
        $this->assertSame(["testlogin1", "12345"], $values);
    }

    public function testSimpleDeleteWithJoinAnotherStructure()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $structureArticles = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsArticles);

        $structureUsers->setJoinAlias("userAlias");
        $structureArticles->setJoinAlias('articlesAlias');

        $joinOptions = [
            'mode' => 'LEFT',
            'as' => 'articles',
            'joinOperatorWhere' => 'AND',
            'useNativeWhere' => false,
            'fieldsCorrelation' => [
                'user_id' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'id',
                    'operator' => 'AND'
                ]
            ]
        ];
        $where = [
            'login' => [
                'value' => 'testlogin1',
                'operator' => 'AND'
            ],
            'password' => [
                'value' => '12345'
            ]
        ];

        $structureUsers->join($structureArticles, $joinOptions);
        $structureUsers->setWhere($where);

        $sqlString = $structureUsers->delete();
        $sqlString = preg_replace('!\s+!', ' ', $sqlString);
        $values = $structureUsers->getValues();
        $this->assertSame("DELETE FROM ptest.users AS userAlias USING ptest.articles articles WHERE userAlias.login= ? AND userAlias.password= ? AND articles.user_id = userAlias.id ",
            $sqlString);
        $this->assertSame(["testlogin1", "12345"], $values);
    }

    public function testSimpleDeleteWithJoinAnotherStructureWithManyFieldsCorrelation()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $structureArticles = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsArticles);

        $structureUsers->setJoinAlias("userAlias");
        $structureArticles->setJoinAlias('articlesAlias');

        $joinOptions = [
            'mode' => 'LEFT',
            'as' => 'articles',
            'joinOperatorWhere' => 'AND',
            'useNativeWhere' => false,
            'fieldsCorrelation' => [
                'user_id' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'id',
                    'operator' => 'AND'
                ],
                'ident' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'status',
                    'operator' => 'AND'
                ]
            ]
        ];

        $where = [
            'login' => [
                'value' => 'testlogin1',
                'operator' => 'AND'
            ],
            'password' => [
                'value' => '12345'
            ]
        ];

        $structureUsers->join($structureArticles, $joinOptions);
        $structureUsers->setWhere($where);

        $sqlString = $structureUsers->delete();
        $sqlString = preg_replace('!\s+!', ' ', $sqlString);
        $values = $structureUsers->getValues();
        $this->assertSame("DELETE FROM ptest.users AS userAlias USING ptest.articles articles WHERE userAlias.login= ? AND userAlias.password= ? AND articles.user_id = userAlias.id AND articles.ident = userAlias.status ",
            $sqlString);
        $this->assertSame(["testlogin1", "12345"], $values);
    }

    public function testSimpleDeleteWithJoinAnotherStructureWithUseNativeWhere()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $structureArticles = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsArticles);

        $structureUsers->setJoinAlias("userAlias");
        $structureArticles->setJoinAlias('articlesAlias');

        $joinOptions = [
            'mode' => 'LEFT',
            'as' => $structureArticles->getJoinAlias(),
            'joinOperatorWhere' => 'AND',
            'useNativeWhere' => true,
            'fieldsCorrelation' => [
                'user_id' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'id',
                    'operator' => 'AND'
                ],
                'ident' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'name',
                    'operator' => 'AND'
                ]
            ]
        ];

        $where = [
            'login' => [
                'value' => 'testlogin1',
                'operator' => 'AND'
            ],
            'password' => [
                'value' => '12345'
            ]
        ];

        $whereArticle = [
            'text' => [
                'value' => 'text1',
                'operator' => 'AND'
            ],
            'ident' => [
                'value' => '1'
            ]
        ];
        $structureUsers->join($structureArticles, $joinOptions);
        $structureUsers->setWhere($where);
        $structureArticles->setWhere($whereArticle);
        $sqlString = $structureUsers->delete();
        $sqlString = preg_replace('!\s+!', ' ', $sqlString);
        $values = $structureUsers->getValues();
        $this->assertSame("DELETE FROM ptest.users AS userAlias USING ptest.articles articlesAlias WHERE userAlias.login= ? AND userAlias.password= ? AND articlesAlias.text= ? AND articlesAlias.ident= ? AND articlesAlias.user_id = userAlias.id AND articlesAlias.ident = userAlias.name ",
            $sqlString);
        $this->assertSame(['testlogin1', '12345', 'text1', '1'], $values);
    }

    /**
     * UPDATE SECTION
     */
    public function testSimpleUpdate()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);

        $updateOptions = [
            'status' => [
                'value' => '7',
            ],
            'name' => [
                'value' => 'Jon'
            ]
        ];
        $structureUsers->setUpdate($updateOptions);
        $sqlString = $structureUsers->update();
        $sqlString = preg_replace('!\s+!', ' ', trim($sqlString));

        $expected = 'UPDATE ptest.users AS userStructure SET status = ?, name = ?';
        $expected = preg_replace('!\s+!', ' ', trim($expected));

        $this->assertSame($expected, trim($sqlString));
        $values = $structureUsers->getValues();
        $this->assertSame(['7', 'Jon'], $values);
    }

    public function testSimpleUpdateWithWhere()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);

        $updateOptions = [
            'status' => [
                'value' => '7',
            ],
            'name' => [
                'value' => 'Jon'
            ]
        ];
        $structureUsers->setUpdate($updateOptions);

        $where = [
            'login' => [
                'value' => 'testlogin1',
                'operator' => 'AND'
            ],
            'password' => [
                'value' => '12345'
            ]
        ];
        $structureUsers->setWhere($where);
        $sqlString = $structureUsers->update();
        $sqlString = preg_replace('!\s+!', ' ', trim($sqlString));
        $values = $structureUsers->getValues();

        $expected = "UPDATE ptest.users AS userStructure SET status = ?, name = ?  WHERE userStructure.login= ?  AND userStructure.password= ?";
        $expected = preg_replace('!\s+!', ' ', trim($expected));

        $this->assertSame($expected, trim($sqlString));
        $this->assertSame(['7', 'Jon', 'testlogin1', '12345'], $values);
    }

    public function testSimpleUpdateWithJoinAnotherStructure()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $structureArticles = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsArticles);

        $updateOptions = [
            'status' => [
                'value' => '7',
            ],
            'name' => [
                'value' => 'Jon'
            ]
        ];
        $structureUsers->setUpdate($updateOptions);

        $structureUsers->setJoinAlias("userAlias");
        $structureArticles->setJoinAlias('articlesAlias');

        $joinOptions = [
            'mode' => 'LEFT',
            'as' => $structureArticles->getJoinAlias(),
            'joinOperatorWhere' => 'AND',
            'useNativeWhere' => false,
            'fieldsCorrelation' => [
                'user_id' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'id',
                    'operator' => 'AND'
                ]
            ]
        ];
        $where = [
            'login' => [
                'value' => 'testlogin1',
                'operator' => 'AND'
            ],
            'password' => [
                'value' => '12345'
            ]
        ];

        $structureUsers->join($structureArticles, $joinOptions);
        $structureUsers->setWhere($where);

        $sqlString = $structureUsers->update();
        $sqlString = trim(preg_replace('!\s+!', ' ', trim($sqlString)));
        $values = $structureUsers->getValues();

        $expected = 'UPDATE ptest.users AS userAlias SET status = ?, name = ? FROM ptest.articles AS articlesAlias  WHERE userAlias.login= ?  AND userAlias.password= ?    AND articlesAlias.user_id = userAlias.id';
        $expected = preg_replace('!\s+!', ' ', trim($expected));

        $this->assertSame($expected, $sqlString);
        $this->assertSame(['7', 'Jon', 'testlogin1', '12345'], $values);
    }

    public function testSimpleUpdateWithJoinAnotherStructureWithManyFieldsCorrelation()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $structureArticles = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsArticles);

        $updateOptions = [
            'status' => [
                'value' => '7',
            ],
            'name' => [
                'value' => 'Jon'
            ]
        ];
        $structureUsers->setUpdate($updateOptions);

        $structureUsers->setJoinAlias("userAlias");
        $structureArticles->setJoinAlias('articlesAlias');

        $joinOptions = [
            'mode' => 'LEFT',
            'as' => $structureArticles->getJoinAlias(),
            'joinOperatorWhere' => 'AND',
            'useNativeWhere' => false,
            'fieldsCorrelation' => [
                'user_id' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'id',
                    'operator' => 'AND'
                ],
                'ident' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'status',
                    'operator' => 'AND'
                ]
            ]
        ];

        $where = [
            'login' => [
                'value' => 'testlogin1',
                'operator' => 'AND'
            ],
            'password' => [
                'value' => '12345'
            ]
        ];

        $structureUsers->join($structureArticles, $joinOptions);
        $structureUsers->setWhere($where);

        $sqlString = $structureUsers->update();
        $sqlString = trim(preg_replace('!\s+!', ' ', trim($sqlString)));
        $values = $structureUsers->getValues();

        $expected = 'UPDATE ptest.users AS userAlias SET status = ?, name = ? FROM ptest.articles AS articlesAlias  WHERE userAlias.login= ?  AND userAlias.password= ?    AND articlesAlias.user_id = userAlias.id  AND articlesAlias.ident = userAlias.status';
        $expected = preg_replace('!\s+!', ' ', trim($expected));

        $this->assertSame($expected, $sqlString);
        $this->assertSame(['7', 'Jon', 'testlogin1', '12345'], $values);
    }

    public function testSimpleUpdateWithJoinAnotherStructureWithUseNativeWhere()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);
        $structureArticles = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsArticles);

        $updateOptions = [
            'status' => [
                'value' => '7',
            ],
            'name' => [
                'value' => 'Jon'
            ]
        ];
        $structureUsers->setUpdate($updateOptions);

        $structureUsers->setJoinAlias("userAlias");
        $structureArticles->setJoinAlias('articlesAlias');

        $joinOptions = [
            'mode' => 'LEFT',
            'as' => $structureArticles->getJoinAlias(),
            'joinOperatorWhere' => 'AND',
            'useNativeWhere' => true,
            'fieldsCorrelation' => [
                'user_id' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'id',
                    'operator' => 'AND'
                ],
                'ident' => [
                    'condition' => '=',
                    'tableAlias' => $structureUsers->getJoinAlias(),
                    'field' => 'name',
                    'operator' => 'AND'
                ]
            ]
        ];

        $where = [
            'login' => [
                'value' => 'testlogin1',
                'operator' => 'AND'
            ],
            'password' => [
                'value' => '12345'
            ]
        ];

        $whereArticle = [
            'text' => [
                'value' => 'text1',
                'operator' => 'AND'
            ],
            'ident' => [
                'value' => '1'
            ]
        ];
        $structureUsers->join($structureArticles, $joinOptions);
        $structureUsers->setWhere($where);
        $structureArticles->setWhere($whereArticle);
        $sqlString = $structureUsers->update();
        $sqlString = preg_replace('!\s+!', ' ', trim($sqlString));
        $values = $structureUsers->getValues();

        $expected = 'UPDATE ptest.users AS userAlias SET status = ?, name = ? FROM ptest.articles AS articlesAlias  WHERE userAlias.login= ?  AND userAlias.password= ?  AND articlesAlias.text= ?  AND articlesAlias.ident= ?    AND articlesAlias.user_id = userAlias.id  AND articlesAlias.ident = userAlias.name';
        $expected = preg_replace('!\s+!', ' ', trim($expected));

        $this->assertSame($expected, $sqlString);
        $this->assertSame(['7', 'Jon', 'testlogin1', '12345', 'text1', '1'], $values);
    }

    /**
     * INSERT SECTION
     */
    public function testSimpleInsert()
    {
        $structureUsers = new \ZXC\Modules\SQL\StructurePGSQL($this->structureParamsUsers);

        $insertFields = [
            'status' => [
                'value' => '7',
            ],
            'name' => [
                'value' => 'Jon'
            ]
        ];
        $structureUsers->setInsert($insertFields);
        $sqlString = $structureUsers->insert();
        $sqlString = preg_replace('!\s+!', ' ', $sqlString);
        $this->assertSame('INSERT INTO ptest.users (status, name) VALUES (?, ?) ', $sqlString);
        $values = $structureUsers->getValues();
        $this->assertSame(['7', 'Jon'], $values);
    }
}