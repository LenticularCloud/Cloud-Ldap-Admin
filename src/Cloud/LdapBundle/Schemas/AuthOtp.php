<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * olcObjectClasses: ( 1.3.6.1.4.1.33578.1.1.4.2.1
 * NAME 'AuthOTP'
 * DESC 'Base class for authenticator configurations'
 * SUP top AUXILIARY
 * MUST ( AuthLabel $ AuthSecret ) )
 * @LDAP\Schema(name="Service")
 */
class AuthOtp
{
    /**
     * olcAttributeTypes: ( 1.3.6.1.4.1.33578.1.1.4.1
     * NAME 'AuthLabel'
     * DESC 'Label for authenticator entry'
     * EQUALITY caseExactMatch
     * ORDERING caseExactOrderingMatch
     * SUBSTR caseExactSubstringsMatch
     * SYNTAX 1.3.6.1.4.1.1466.115.121.1.44 )
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $authLabel;

    /**
     * olcAttributeTypes: ( 1.3.6.1.4.1.33578.1.1.4.2
     * NAME 'AuthSecret'
     * DESC 'Authenticator shared secret'
     * EQUALITY octetStringMatch
     * ORDERING octetStringOrderingMatch
     * SUBSTR octetStringSubstringsMatch
     * SYNTAX 1.3.6.1.4.1.1466.115.121.1.40
     * SINGLE-VALUE )
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $authSecret;

    public function __construct()
    {
        $this->authLabel = new Attribute();
        $this->authSecret = new Attribute();
    }

    /**
     * @return Attribute
     *
     * @Assert\NotBlank()
     */
    public function getAuthSecret()
    {
        return $this->authSecret->get();
    }

    /**
     * @param Attribute $authSecret
     * @return CloudService
     */
    public function setAuthSecret($authSecret)
    {
        $this->authSecret->set($authSecret);
        return $this;
    }

    /**
     * @return string
     *
     * @Assert\NotBlank()
     */
    public function getAuthLabel()
    {
        return $this->authLabel->get();
    }

    /**
     * @param string $authLabel
     * @return CloudService
     */
    public function setAuthLabel($authLabel)
    {
        $this->authLabel->set($authLabel);
        return $this;
    }
}