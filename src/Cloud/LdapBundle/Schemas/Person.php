<?php
namespace Cloud\LdapBundle\Schemas;

/**
 * DESC 'RFC2256: a person'
 * SUP top STRUCTURAL
 * MUST ( sn $ cn )
 * MAY ( userPassword $ telephoneNumber $ seeAlso $ description ) )
 */
interface Person
{
    public function getSn();
    public function setSn($sn);

    public function getCn();
    public function setCn($cn);

    public function getUserPassword();
    public function setUserPassword($userPassword);
}