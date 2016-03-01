<?php
namespace Cloud\LdapBundle\Security;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Entity\Password;

/**
 *
 * @author tuxcoder
 *
 */
class CryptEncoder implements LdapPasswordEncoderInterface
{

    /**
     * (non-PHPdoc)
     *
     * @see \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface::encodePassword()
     */
    static public function encodePassword(Password $password)
    {
        $rounds = 60000; // incresed rounds for harder bruteforce

        $salt = "";
        if ($password->getId() != null && $password->getId() != "") {
            $salt = self::getRandomeSalt(16 - strlen($password->getId()));
            $salt = $password->getId() . ($password->isMasterPassword() ? '+' : '=') . $salt;
        } else {
            $salt = 'default=' . self::getRandomeSalt();
        }

        $hash = crypt($password->getPasswordPlain(), '$6$rounds=' . $rounds . '$' . $salt . '$');
        $password->setHash('{crypt}' . $hash);
        $password->setPasswordPlain(null);
        $password->setEncoder(CryptEncoder::class);
    }

    /**
     * generate randome string
     *
     * @param number $length
     */
    static private function getRandomeSalt($length = 9)
    {
        //@TODO use openssl_random_pseudo_bytes as random

        $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

        $string = "";
        $char_num = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(0, $char_num - 1), 1);
        }

        return $string;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface::isPasswordValid()
     */
    static public function isPasswordValid(Password $password)
    {
        if (substr($password->getHash(), 0, 7) != '{crypt}')
            return false;
        $hash = substr($password->getHash(), 7);
        if (crypt($password->getPasswordPlain(), $hash) === $hash) {
            return true;
        }

        return false;
    }


    /**
     * (non-PHPdoc)
     * @see \Cloud\LdapBundle\Security\PasswordEncoderInterface::parsePassword()
     */
    static public function parsePassword(Attribute $password_hash)
    {
        $password = new Password();
        $password->setAttribute($password_hash);
        $matches = null;
        $found = preg_match('#^{crypt}\$\d\$(rounds=\d+\$)?([0-9a-zA-Z_-]+)?(=|\+)[0-9a-zA-Z_-]+\$[^\$]*$#', $password_hash->get(), $matches);
        if ($found === 1) {
            $password->setId($matches[2]);
            $password->setMasterPassword($matches[3] === '+');
        } else {
            return null;
        }
        $password->setEncoder(CryptEncoder::class);
        return $password;
    }
}
