<?php

namespace App\Service\Type;

use App\Service\Types;
use GraphQL\Type\Definition\InputObjectType;

class CreateUserType extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'description' => 'Добавление пользователя',
            'fields' => function() {
                return [
                    'username' => [
                        'type' => Types::string(),
                        'description' => 'логин пользователя'
                    ],
                    'password' => [
                        'type' => Types::string(),
                        'description' => 'пароль пользователя'
                    ],
                    'firstName' => [
                        'type' => Types::string(),
                        'description' => 'имя пользователя'
                    ],
                    'lastName' => [
                        'type' => Types::string(),
                        'description' => 'фамилия пользователя'
                    ]
                ];
            }
        ];
        
        parent::__construct($config);
    }
}