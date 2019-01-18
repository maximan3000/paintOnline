<?php

namespace App\Service\Type;

use App\Service\Types;
use App\Service\Logic\RegisterUser;
use GraphQL\Type\Definition\ObjectType;

class MutationType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'register' => [
                        'type' => Types::user(),
                        'description' => 'регистрация пользователя',
                        'args' => [
                            'user' => Types::createUser()
                        ],
                        'resolve' => function ($root, $args) {
                            $user = RegisterUser::register($args['user']);
                            return $user;
                        }
                    ]
                ];
            }
        ];

        parent::__construct($config);
    }
}