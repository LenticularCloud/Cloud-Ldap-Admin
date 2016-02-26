<?php
namespace Cloud\LdapBundle\Security;

use Cloud\LdapBundle\Entity\Password;

/**
 *
 * @author tuxcoder
 *
 */
class NtEncoder implements LdapPasswordEncoderInterface
{

    /**
     * (non-PHPdoc)
     *
     * @see \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface::encodePassword()
     */
    public function encodePassword(Password $password)
    {
        $password->setHash($this->NTLMHash($password->getPasswordPlain()));
        $password->setPasswordPlain(null);
        $password->setId('default');
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface::isPasswordValid()
     */
    public function isPasswordValid(Password $password)
    {
        $hash = $password->getHash();
        if ($this->NTLMHash($password->getPasswordPlain()) === $hash) {
            return true;
        }

        return false;
    }


    /**
     * (non-PHPdoc)
     * @see \Cloud\LdapBundle\Security\PasswordEncoderInterface::parsePassword()
     */
    public function parsePassword($password_hash)
    {
        $password = new Password();
        $password->setHash($password_hash);
        $password->setId('default');

        if(preg_match('#^[0-9A-F]$#',$password_hash)===1) {
            $password->setMasterPassword(true);
        }elseif(preg_match('#^[0-9a-f]$#',$password_hash)===1) {
            $password->setMasterPassword(false);
        }
        return $password;
    }

    function NTLMHash($Input) {
        // Convert the password from UTF8 to UTF16 (little endian)
        $Input=iconv('UTF-8','UTF-16LE',$Input);

        // Encrypt it with the MD4 hash
        $MD4Hash=bin2hex(mhash(MHASH_MD4,$Input));

        // You could use this instead, but mhash works on PHP 4 and 5 or above
        // The hash function only works on 5 or above
        //$MD4Hash=hash('md4',$Input);

        // Make it uppercase, not necessary, but it's common to do so with NTLM hashes
        $NTLMHash=strtoupper($MD4Hash);

        // Return the result
        return($NTLMHash);
    }
}
