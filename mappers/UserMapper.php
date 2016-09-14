<?php

namespace Migration\Mappers;

/**
 * Class UserMapper Mapping for table 'user'
 * @package Migration\Mappers
 */
class UserMapper extends BaseMapper
{
    public $mapping = [

        'user' => [

            'table' => 'user',

            'remember_last_insert_id' => true,

            'constants'     => [
                '%NULL%'            => 'NULL',
                '%default_project%' => 1,
            ],

            'mapFields'   => [
                'id_user'       => '%NULL%',
                'id_project'    => '%default_project%',
                'password_old'  => 'password',
                'id_user_old'   => 'id_user',
            ],

            /**
             * This functions must be defined in Helper class
             *
             */
            'mapThroughFunctions' => [
                'email'         => [
                    'function' => 'encryptEmailHelper',
                    'params' =>
                        ['email']
                ],
                'email_hash'    => [
                    'function' => 'encryptEmailHasherHelper',
                    'params' =>
                        ['email']
                ],
                'newsletter'    => [
                    'function' => 'newsletterTransform',
                    'params' =>
                        ['newsletter']
                ],
                'status'        => [
                    'function' => 'statusTransformer',
                    'params' => ['status']
                ]
            ],
        ],

        'user_location_information' => [

            'mapFields' => [
                'id_user_location_information'  => '%NULL%',
                'id_user'                       => '%last_insert_id%',
                'name'                          => 'name',
                'surname'                       => 'surname',
                'website'                       => 'website',
                'ip'                            => 'ip',
                'locale_id_locale'              => '%locale_id_locale%',
            ],

            'mapThroughFunctions' => [
                'country_id_country' => [
                    'function' => 'countryMapperHelper',
                    'params' =>
                        ['country']
                ],
            ],

            'constants' => [
                '%NULL%'                => 'NULL',
                '%locale_id_locale%'    => '7',
                '%last_insert_id%'      => 'last_insert_id()'
            ]
        ],

    ];

}

/**
 * ALTER TABLES :
 *
 *
 *
 *
 */
