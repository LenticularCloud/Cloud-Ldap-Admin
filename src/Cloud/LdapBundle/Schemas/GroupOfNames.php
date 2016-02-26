<?php
namespace Cloud\LdapBundle\Schemas;

/**
 * DESC 'RFC2256: a group of names (DNs)'
 * SUP top STRUCTURAL
 * MUST ( member $ cn )
 * MAY ( businessCategory $ seeAlso $ owner $ ou $ o $ description ) )
 */
interface GroupOfNames
{
    public function getCn();
    public function setCn($cn);

    public function getMember();
    public function setMemeber($member);

}