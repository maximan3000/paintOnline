<?php
namespace App\Service\Logic;

use GraphQL\Error\ClientAware;

class SafeException extends \Exception implements ClientAware
{
    public function isClientSafe()
    {
        return true;
    }
    
    public function getCategory()
    {
        return 'businessLogic';
    }
}