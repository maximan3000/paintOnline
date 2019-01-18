<?php

namespace App\Service\Type;

use App\Service\Types;
use App\Service\Logic\AuthUser;
use GraphQL\Type\Definition\ObjectType;

class QueryType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'authentificate' => [
                        'type' => Types::user(),
                        'description' => 'аутентификация пользователя по логину и паролю',
                        'args' => [
                            'username' => Types::string(),
                            'password' => Types::string()
                        ],
                        'resolve' => function ($root, $args) {
                            $user = AuthUser::auth($args);
                            return $user;
                        }
                    ]
                ];
            }
        ];

        parent::__construct($config);
    }
}