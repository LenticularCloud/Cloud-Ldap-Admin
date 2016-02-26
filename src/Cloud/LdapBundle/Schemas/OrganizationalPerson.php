<?php
/**
 * Created by PhpStorm.
 * User: norbert
 * Date: 2/15/16
 * Time: 8:51 PM
 */

namespace Cloud\LdapBundle\Schemas;

/**
 * DESC 'RFC2256: an organizational person'
 * SUP person STRUCTURAL
 * MAY ( title $ x121Address $ registeredAddress $ destinationIndicator $
 * preferredDeliveryMethod $ telexNumber $ teletexTerminalIdentifier $
 * telephoneNumber $ internationaliSDNNumber $
 * facsimileTelephoneNumber $ street $ postOfficeBox $ postalCode $
 * postalAddress $ physicalDeliveryOfficeName $ ou $ st $ l ) )
 */
interface OrganizationalPerson extends Person
{

}