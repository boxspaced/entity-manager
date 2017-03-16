<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Mapper\MongoQueryBuilder;
use Boxspaced\EntityManager\Mapper\Query;
use Boxspaced\EntityManager\Exception;
use Boxspaced\EntityManager\Entity\AbstractEntity;

class MongoQueryBuilderTest extends \PHPUnit_Framework_TestCase
{

    protected $config;

    public function setUp()
    {
        $this->config = [
            'types' => [
                'Model\Article' => [
                    'mapper' => [
                        'params' => [
                            'collection' => 'article',
                            'document_fields' => [
                                'id' => '_id',
                                'title' => 'title_field',
                                'author' => 'author_id',
                                'publisher' => 'publisher_id',
                            ],
                        ],
                    ],
                    'entity' => [
                        'fields' => [
                            'id' => [
                                'type' => AbstractEntity::TYPE_INT,
                            ],
                            'slug' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'title' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'author' => [
                                'type' => 'Model\User',
                            ],
                            'publisher' => [
                                'type' => 'Model\User',
                            ],
                            'status' => [
                                'type' => 'Model\ContentStatus',
                            ],
                        ],
                    ],
                ],
                'Model\User' => [
                    'mapper' => [
                        'params' => [
                            'collection' => 'user',
                            'document_fields' => [
                                'id' => '_id',
                                'name' => 'description',
                                'longDesc' => 'long_description',
                                'type' => 'type_field',
                            ],
                        ],
                    ],
                    'entity' => [
                        'fields' => [
                            'id' => [
                                'type' => AbstractEntity::TYPE_INT,
                            ],
                            'desc' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'longDesc' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'type' => [
                                'type' => 'UserType',
                            ],
                        ],
                    ],
                ],
                'Model\ContentStatus' => [
                    'mapper' => [
                        'params' => [
                            'embedded' => true,
                            'document_fields' => [
                                'text' => 'text_field',
                                'lastModified' => 'last_modified_field',
                            ],
                        ],
                    ],
                    'entity' => [
                        'fields' => [
                            'text' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'lastModified' => [
                                'type' => AbstractEntity::TYPE_DATETIME,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testTest()
    {
        $query = new Query();
        $query->field('status.text')->eq('PUBLISHED');
        $query->order('slug', Query::ORDER_ASC);
        $query->order('status.lastModified', Query::ORDER_DESC);
        $query->paging(100, 10);

        $builder = new MongoQueryBuilder($this->config);
        $mongoQuery = $builder->buildFromMapperQuery('Model\Article', $query);

        $this->assertEquals(
            [
                'status.text_field' => [
                    '$eq' => 'PUBLISHED',
                ],
            ],
            $mongoQuery->getFilters()
        );

        $this->assertEquals(
            [
                'sort' => [
                    'slug' => 1,
                    'status.last_modified_field' => -1,
                ],
                'limit' => 10,
                'skip' => 100,
            ],
            $mongoQuery->getOptions()
        );
    }

}
