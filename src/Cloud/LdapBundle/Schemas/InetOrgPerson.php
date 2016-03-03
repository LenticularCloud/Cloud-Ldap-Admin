<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Symfony\Component\Validator\Constraints as Assert;

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
     * attributetype ( 2.16.840.1.113730.3.1.241
     * NAME 'displayName'
     * DESC 'RFC2798: preferred name to be used when displaying entries'
     * EQUALITY caseIgnoreMatch
     * SUBSTR caseIgnoreSubstringsMatch
     * SYNTAX 1.3.6.1.4.1.1466.115.121.1.15
     * SINGLE-VALUE )
     *
     * @var Attribute
     * @LDAP\Attribute(type="string")
     */
    private $displayName;
    /**
     * attributetype ( 2.5.4.42 NAME ( 'givenName' 'gn' )
     * DESC 'RFC2256: first name(s) for which the entity is known by'
     * SUP name )
     *
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

    public function __construct()
    {
        parent::__construct();
        $this->displayName = new Attribute();
        $this->givenName = new Attribute();
        $this->mail = new Attribute();
        $this->mobile = new Attribute();
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
     *
     * @Assert\Email()
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
}