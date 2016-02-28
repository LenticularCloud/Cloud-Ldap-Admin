<?php
namespace Cloud\LdapBundle\Schemas;


/**
 * DESC 'Samba Group Mapping'
 * MUST ( gidNumber $ sambaSID $ sambaGroupType )
 * MAY  ( displayName $ description $ sambaSIDList ))
 * @LDAP\Schema()
 */
interface SambaGroupMapping
{

}