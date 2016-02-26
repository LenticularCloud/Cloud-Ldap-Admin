<?php
namespace Cloud\LdapBundle\Schemas;


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
 */
interface InetOrgPerson extends OrganizationalPerson
{
    public function getUid();

    public function setUid($uid);

    public function getDisplayName();

    public function setDisplayName($displayName);

    public function getMail();

    public function setMail($mail);

    public function getGivenname();

    public function setGivenname($givenname);

}