<?php
/**
 * Created by PhpStorm.
 * User: norbert
 * Date: 2/16/16
 * Time: 12:52 AM
 */

namespace Cloud\LdapBundle\Entity\Service;


class AbstractService
{
    abstract function isMasterpasswordsEnable();

    abstract function addPassword();

    abstract function removePassword();

    abstract function getPasswords();
}