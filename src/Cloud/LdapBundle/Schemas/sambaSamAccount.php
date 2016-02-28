<?php
namespace Cloud\LdapBundle\Schemas;


/**
 * DESC 'Samba 3.0 Auxilary SAM Account'
 * MUST ( uid $ sambaSID )
 * MAY  ( cn $ sambaLMPassword $ sambaNTPassword $ sambaPwdLastSet $
 * sambaLogonTime $ sambaLogoffTime $ sambaKickoffTime $
 * sambaPwdCanChange $ sambaPwdMustChange $ sambaAcctFlags $
 * displayName $ sambaHomePath $ sambaHomeDrive $ sambaLogonScript $
 * sambaProfilePath $ description $ sambaUserWorkstations $
 * sambaPrimaryGroupSID $ sambaDomainName $ sambaMungedDial $
 * sambaBadPasswordCount $ sambaBadPasswordTime $
 * sambaPasswordHistory $ sambaLogonHours))
 * @LDAP\Schema()
 */
interface SambaSamAccount
{
    public function getUid();
    public function setUid($uid);

    public function getSambaSID();
    public function setSambaSID($sambaSID);


    public function getSambaAcctFlags();
    public function setSambaAcctFlags($sambaAcctFlags);

    public function getSambaNTPassword();
    public function setSambaNTPassword($sambaNTPassword);


}