<?php
/**
 * Created by PhpStorm.
 * User: norbert
 * Date: 2/16/16
 * Time: 12:34 AM
 */

namespace Cloud\LdapBundle\Entity;


use Cloud\LdapBundle\Schemas\SambaSamAccount;

class FreeRadiusService implements SambaSamAccount
{

    public function __call($name, $arguments)
    {

        // TODO: Implement __call() method.
    }
}