<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 18.07.2018
 * Time: 6:34
 */

class UserExistsException extends Exception
{
    public function __construct()
    {
        parent::__construct('UserExistsException');
    }
}