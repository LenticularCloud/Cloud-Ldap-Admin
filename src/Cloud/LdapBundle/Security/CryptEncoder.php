<?php
namespace Cloud\LdapBundle\Security;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Entity\Password;

/**
 *
 * @author tuxcoder
 *
 * Format: {crypt}$<type>$rounds=<rounds>$<id>[+|=]<salt>$<hash>$
 *         + stands for masterpassword
 *         = for local password
 * Example hash: {crypt}$6$rounds=60000$master=rIAwMhy8d$4LA5OQUnMXZAOO8/r8s1XgUp0MLVi4sURWRAdPYEqsZ294q5u3dZs63Q7AXaw71P60wpr2idYo3W958GgeTQb1
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
            $salt = $password->getId().($password->isMasterPassword() ? '+' : '=').$salt;
        } else {
            $salt = 'default='.self::getRandomeSalt();
        }

        $hash = crypt($password->getPasswordPlain(), '$6$rounds='.$rounds.'$'.$salt.'$');
        $password->setHash('{crypt}'.$hash);
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
        $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

        $string = "";
        $char_num = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            if (function_exists('random_int')) { // for php7.0 +
                $rand = random_int(0, $char_num - 1);
            } else {
                $rand = rand(0, $char_num - 1);
            }
            $string .= substr($chars, $rand, 1);
        }

        return $string;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface::isPasswordValid()
     */
    static public function isPasswordValid(Password $password)
    {
        if (substr($password->getHash(), 0, 7) != '{crypt}') {
            return false;
        }
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
        $found = preg_match('#^{crypt}\$\d\$(rounds=\d+\$)?([0-9a-zA-Z_-]+)?(=|\+)[0-9a-zA-Z_-]+\$[^\$]*$#',
            $password_hash->get(), $matches);
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
