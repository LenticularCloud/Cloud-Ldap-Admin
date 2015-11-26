<?php
namespace Cloud\LdapBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Security\AsteriskEncoder;

class ServiceAsterisk extends Service
{
    
    public function __construct($name)
    {
        parent::__construct($name);
        $this->encoder=new AsteriskEncoder();
    }
}
