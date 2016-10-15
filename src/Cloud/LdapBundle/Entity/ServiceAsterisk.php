<?php
namespace Cloud\LdapBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Security\AsteriskEncoder;

class ServiceAsterisk extends Service
{

    /**
     * {@inheritdoc}
     */
    public function getPasswordEncoder()
    {
        return AsteriskEncoder::class;
    }
}
