<?php

namespace Cloud\LdapBundle\Schemas;

/**
 * DESC 'Abstraction of a group of accounts'
 * SUP top STRUCTURAL
 * MUST ( cn $ gidNumber )
 * MAY ( userPassword $ memberUid $ description ) )
 * @LDAP\Schema()
 */
interface PosixGroup
{
    public function getCn();
    public function setCn($cn);

    public function getGidNumber();
    public function setGidNumber();

    public function getMemberUids();
    public function addMemberUids($uid);
    public function removeMemberUids($uid);

    public function getDescription();
    public function setDescription($description);
}