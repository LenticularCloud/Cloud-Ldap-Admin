<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * SUP top AUXILIARY
 * DESC 'Samba 3.0 Auxilary SAM Account'
 * MUST ( uid $ sambaSID )
 * MAY  ( cn $ sambaLMPassword $ sambaNTPassword $ sambaPwdLastSet $
 * sambaLogonTime $ sambaLogoffTime $ sambaKickoffTime $
 * sambaPwdCanChange $ sambaPwdMustChange $ sambaAcctFlags $
 * displayName $ sambaHomePath $ sambaHomeDrive $ sambaLogonScript $
 * sambaProfilePath $ description $ sambaUserWorkstations $
 * sambaPrimaryGroupSID $ sambaDomainName $ sambaMungedDial $
 * sambaBadPasswordCount $ sambaBadPasswordTime $
 * sambaPasswordHistory $ sambaLogonHours))
 * @LDAP\Schema()
 */
class SambaSamAccount
{

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $uid;

    /**
     * DESC 'Security ID'
     * EQUALITY caseIgnoreIA5Match
     * SUBSTR caseExactIA5SubstringsMatch
     * SYNTAX 1.3.6.1.4.1.1466.115.121.1.26{64} SINGLE-VALUE
     *
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $sambaSID;

    /**
     * DESC 'LanManager Password'
     * EQUALITY caseIgnoreIA5Match
     * SYNTAX 1.3.6.1.4.1.1466.115.121.1.26{32} SINGLE-VALUE
     *
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $sambaLMPassword;


    /**
     * ( 1.3.6.1.4.1.7165.2.1.25
     * NAME 'sambaNTPassword'
     * DESC 'MD4 hash of the unicode password'
     * EQUALITY caseIgnoreIA5Match
     * SYNTAX 1.3.6.1.4.1.1466.115.121.1.26{32}
     * SINGLE-VALUE )
     *
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $sambaNTPassword;

    /**
     * @var Attribute
     *
     * @LDAP\Attribute(type="string")
     */
    private $displayName;

    public function __construct()
    {

    }

    /**
     * @return Attribute
     *
     * @Assert\NotBlank()
     */
    public function getUid()
    {
        return $this->uid->get();
    }

    /**
     * @param Attribute $uid
     * @return SambaSamAccount
     */
    public function setUid($uid)
    {
        $this->uid->set($uid);
        return $this;
    }

    /**
     * @return Attribute
     *
     * @Assert\NotBlank()
     */
    public function getSambaSID()
    {
        return $this->sambaSID->get();
    }

    /**
     * @param Attribute $sambaSID
     * @return SambaSamAccount
     */
    public function setSambaSID($sambaSID)
    {
        $this->sambaSID->set($sambaSID);
        return $this;
    }

    /**
     * @return Attribute
     */
    public function getSambaLMPassword()
    {
        return $this->sambaLMPassword->get();
    }

    /**
     * @param Attribute $sambaLMPassword
     * @return SambaSamAccount
     */
    public function setSambaLMPassword($sambaLMPassword)
    {
        $this->sambaLMPassword->set($sambaLMPassword);
        return $this;
    }

    /**
     * @return Attribute
     */
    public function getSambaNTPassword()
    {
        return $this->sambaLMPassword->get();
    }

    /**
     * @param Attribute $sambaLMPassword
     * @return SambaSamAccount
     */
    public function setSambaNTPassword($sambaLMPassword)
    {
        $this->sambaLMPassword->set($sambaLMPassword);
        return $this;
    }

    /**
     * @return Attribute
     */
    public function getDisplayName()
    {
        return $this->displayName->get();
    }

    /**
     * @param Attribute $displayName
     * @return SambaSamAccount
     */
    public function setDisplayName($displayName)
    {
        $this->displayName->set($displayName);
        return $this;
    }


}