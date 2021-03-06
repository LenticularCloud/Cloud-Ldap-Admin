<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use \InvalidArgumentException;
use Cloud\LdapBundle\Security\LdapPasswordEncoderInterface;
use Cloud\LdapBundle\Security\CryptEncoder;
use Cloud\LdapBundle\Schemas;

class SeafileService extends Service
{

    protected function serviceEnabled()
    {
        parent::serviceEnabled();
        $this->setMasterPasswordEnabled(true);

    }
}
