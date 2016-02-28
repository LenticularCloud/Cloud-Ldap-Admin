<?php
namespace Cloud\LdapBundle\Security;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Entity\Password;

interface LdapPasswordEncoderInterface {

    /**
     * function das sets the hash value in the password object
     * the function also delete the plaintext password
     *
     * @param Password $password
     */
    public function encodePassword(Password $password);
    
    /**
     * 
     * 
     * @param Password $password
     * @return boolean true if password is correct
     */
    public function isPasswordValid(Password $password);
    
    
    /**
     * gets a Password object from a hash string 
     *
     * @param Attribute $password_hash
     * @return Password|null if can't be parsed return null
     */
    public function parsePassword(Attribute $password_hash);
}