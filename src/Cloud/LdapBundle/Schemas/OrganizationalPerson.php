<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;

/**
 * DESC 'RFC2256: an organizational person'
 * SUP person STRUCTURAL
 * MAY ( title $ x121Address $ registeredAddress $ destinationIndicator $
 * preferredDeliveryMethod $ telexNumber $ teletexTerminalIdentifier $
 * telephoneNumber $ internationaliSDNNumber $
 * facsimileTelephoneNumber $ street $ postOfficeBox $ postalCode $
 * postalAddress $ physicalDeliveryOfficeName $ ou $ st $ l ) )
 * @LDAP\Schema()
 */
class OrganizationalPerson extends Person
{

    /**
     * @var Attribute
     *
     * @Ldap\Attribute(type="string")
     */
    private $registeredAddress;

    public function __construct()
    {
        parent::__construct();
        $this->registeredAddress=new Attribute();
    }

    /**
     * @return Attribute
     */
    public function getRegisteredAddress()
    {
        return $this->registeredAddress->get();
    }

    /**
     * @param Attribute $registeredAddress
     * @return OrganizationalPerson
     */
    public function setRegisteredAddress($registeredAddress)
    {
        $this->registeredAddress->set($registeredAddress);
        return $this;
    }


}