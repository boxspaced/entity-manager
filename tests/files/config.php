<?php

use EntityManager\Mapper\Conditions\Conditions;

return [
    'db' => [
        'driver' => 'Pdo_Sqlite',
        'database' => '/home/rwallwork/sqlite/em.db',
    ],
    'types' => [
        'Item' => [
            'mapper' => [
                'params' => [
                    'table' => 'item',
                    'columns' => [
                        'id' => 'i_id',
                        'desc' => 'description',
                        'longDesc' => 'long_description',
                        'author' => 'author_id',
                        'publisher' => 'publisher_id',
                        'status' => 'status_id',
                        'versionOf' => 'version_of_id',
                    ],
                ],
            ],
            'builder' => [
                'references' => [
                    'author' => [
                        'type' => 'User',
                    ],
                    'publisher' => [
                        'type' => 'User',
                    ],
                    'status' => [
                        'type' => 'ContentStatus',
                    ],
                    'versionOf' => [
                        'type' => 'Item',
                    ],
                ],
            ],
        ],
        'User' => [
            'mapper' => [
                'params' => [
                    'table' => 'user',
                    'columns' => [
                        'id' => 'u_id',
                        'desc' => 'description',
                        'longDesc' => 'long_description',
                        'type' => 'type_id',
                    ],
                ],
            ],
            'builder' => [
                'references' => [
                    'type' => [
                        'type' => 'UserType',
                    ],
                ],
            ],
        ],
        'UserType' => [
            'mapper' => [
                'params' => [
                    'table' => 'user_type',
                    'columns' => [
                        'id' => 'ut_id',
                        'desc' => 'description',
                        'longDesc' => 'long_description',
                    ],
                ],
            ],
        ],
        'ContentStatus' => [
            'mapper' => [
                'params' => [
                    'table' => 'content_status',
                    'columns' => [
                        'id' => 'cs_id',
                        'desc' => 'description',
                        'longDesc' => 'long_description',
                        'type' => 'type_id',
                    ],
                ],
            ],
            'builder' => [
                'references' => [
                    'type' => [
                        'type' => 'ContentStatusType',
                    ],
                ],
            ],
        ],
        'ContentStatusType' => [
            'mapper' => [
                'params' => [
                    'table' => 'content_status_type',
                    'columns' => [
                        'id' => 'cst_id',
                        'desc' => 'description',
                        'longDesc' => 'long_description',
                    ],
                ],
            ],
        ],
        'EntityManager\\Test\\Double\\Entity' => [

        ],
        // @todo probably don't need all the config below as mapper
        // is specific to entity not generic (can all be in mapper)
        /*'Customer' => [
            'mapper' => [
                'strategy' => function() {
                    return new \My\Mapper\CustomerStrategy();
                },
                'params' => [
                    'wsdl' => 'https://customer-service.example.com/service.asmx?wsdl',
                    'methods' => [
                        'find' => 'Customer_Get',
                        'findOne' => 'Customer_Search',
                        'findAll' => 'Customer_Search',
                        'create' => 'Customer_UpdateCreate',
                        'update' => 'Customer_UpdateCreate',
                        'delete' => 'Customer_Delete',
                    ],
                    'params' => [
                        'name' => 'name',
                        'guid' => 'GUID',
                        'status' => 'state',
                        'address' => 'addressID', // @todo would probably have address object embedded in customer response
                    ],
                ],
            ],
            'builder' => [
                'references' => [
                    'address' => [
                        'type' => 'CustomerAddress',
                    ],
                ],
            ],
        ],*/
    ],
];
