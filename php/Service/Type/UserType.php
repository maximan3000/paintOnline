<?php

namespace App\Service\Type;

use App\Service\Types;
use GraphQL\Type\Definition\ObjectType;

class UserType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'description' => 'Аутентификация пользователя',
            'fields' => function() {
                return [
                    'username' => [
                        'type' => Types::string(),
                        'description' => 'логин пользователя'
                    ],
                    'firstName' => [
                        'type' => Types::string(),
                        'description' => 'имя пользователя'
                    ],
                    'lastName' => [
                        'type' => Types::string(),
                        'description' => 'фамилия товара'
                    ],
                    'token' => [
                        'type' => Types::string(),
                        'description' => 'токен аутентификации'
                    ]
                ];
            }
        ];

        parent::__construct($config);
    }
}