<?php
namespace Cloud\LdapBundle\Schemas;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * olcObjectClasses: ( 1.3.6.1.4.1.24552.500.1.1.2.0
 * NAME 'ldapPublicKey'
 * SUP top AUXILIARY
 * DESC 'MANDATORY: OpenSSH LPK objectclass'
 * MAY ( sshPublicKey $ uid )
 * )
 *
 * @Ldap\Schema()
 */
class LdapPublicKey
{

    /**
     * @var ArrayCollection
     *
     * @LDAP\Attribute(type="string")
     */
    private $uid;

    /**
     * olcAttributeTypes: ( 1.3.6.1.4.1.24552.500.1.1.1.13
     * NAME 'sshPublicKey'
     * DESC 'MANDATORY: OpenSSH Public key'
     * EQUALITY octetStringMatch
     * SYNTAX 1.3.6.1.4.1.1466.115.121.1.40 )
     *
     * @var ArrayCollection
     *
     * @LDAP\Attribute(name="sshPublicKey",type="array")
     */
    private $sshPublicKeys;

    public function __construct()
    {
        $this->uid = new ArrayCollection();
        $this->uid = new Attribute();
    }

    /**
     * @return ArrayCollection
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param ArrayCollection $uid
     * @return OpensshLpk
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSshPublicKeys()
    {
        return array_map(function ($attribute) {
            return $attribute->get();
        }, $this->sshPublicKeys->toArray());
    }

    /**
     * @param string $sshPublicKey
     * @return OpensshLpk
     */
    public function addSshPublicKey($sshPublicKey)
    {
        $this->removeSshPublicKey($sshPublicKey);
        $this->sshPublicKeys->add(new Attribute($sshPublicKey));

        return $this;
    }

    /**
     * @param string $sshPublicKey
     * @return OpensshLpk
     */
    public function removeSshPublicKey($sshPublicKey)
    {
        foreach ($this->sshPublicKeys as $sshPublicKey) {
            if ($sshPublicKey->get() == $sshPublicKey) {
                $this->sshPublicKeys->removeElement($sshPublicKey);

                return $this;
            }
        }

        return $this;
    }
}