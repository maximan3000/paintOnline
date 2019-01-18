<?php

namespace App\Service;

use App\Service\Type\QueryType;
use App\Service\Type\UserType;
use App\Service\Type\MutationType;
use App\Service\Type\CreateUserType;
use GraphQL\Type\Definition\Type;

class Types
{
    private static $query;
    private static $user;

    private static $mutation;
    private static $createUser;

    public static function query()
    {
        return self::$query ?: (self::$query = new QueryType());
    }

    public static function user()
    {
        return self::$user ?: (self::$user = new UserType());
    }

    public static function mutation()
    {
        return self::$mutation ?: (self::$mutation = new MutationType());
    }

    public static function createUser()
    {
        return self::$createUser ?: (self::$createUser = new CreateUserType());
    }

    public static function string()
    {
        return Type::string();
    }

}