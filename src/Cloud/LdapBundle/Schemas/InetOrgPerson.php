<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;

/**
 * DESC 'RFC2798: Internet Organizational Person'
 * SUP organizationalPerson
 * STRUCTURAL
 * MAY (
 * audio $ businessCategory $ carLicense $ departmentNumber $
 * displayName $ employeeNumber $ employeeType $ givenName $
 * homePhone $ homePostalAddress $ initials $ jpegPhoto $
 * labeledURI $ mail $ manager $ mobile $ o $ pager $
 * photo $ roomNumber $ secretary $ uid $ userCertificate $
 * x500uniqueIdentifier $ preferredLanguage $
 * userSMIMECertificate $ userPKCS12 )
 * )
 * @LDAP\Schema()
 */
class InetOrgPerson extends OrganizationalPerson
{

    /**
     * @var Attribute
     * @LDAP\Attribute(type="string")
     */
    private $displayName;
    /**
     * @var Attribute
     * @LDAP\Attribute(type="string")
     */
    private $givenName;

    /**
     * @var Attribute
     * @LDAP\Attribute(type="string")
     */
    private $mail;

    /**
     * @var Attribute
     * @LDAP\Attribute(type="string")
     */
    private $mobile;

    /**
     * @var Attribute
     * @LDAP\Attribute(type="string")
     */
    private $uid;

    public function __construct()
    {
        parent::__construct();
        $this->displayName = new Attribute();
        $this->givenName = new Attribute();
        $this->mail = new Attribute();
        $this->mobile = new Attribute();
        $this->uid = new Attribute();
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName->get();
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName->set($displayName);
    }

    /**
     * @return string
     */
    public function getGivenName()
    {
        return $this->givenName->get();
    }

    /**
     * @param string $givenName
     */
    public function setGivenName($givenName)
    {
        $this->givenName->set($givenName);
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail->get();
    }

    /**
     * @param string $mail
     */
    public function setMail($mail)
    {
        $this->mail->set($mail);
    }

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile->get();
    }

    /**
     * @param string $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile->set($mobile);
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid->get();
    }

    /**
     * @param string $uid
     */
    public function setUid($uid)
    {
        $this->uid->set($uid);
    }
}