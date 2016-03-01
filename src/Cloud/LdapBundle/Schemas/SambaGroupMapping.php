<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DESC 'Samba Group Mapping'
 * MUST ( gidNumber $ sambaSID $ sambaGroupType )
 * MAY  ( displayName $ description $ sambaSIDList ))
 * @LDAP\Schema()
 */
interface SambaGroupMapping
{

}