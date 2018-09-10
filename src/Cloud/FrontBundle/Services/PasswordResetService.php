<?php
namespace Cloud\FrontBundle\Services;


use Cloud\LdapBundle\Entity\User;


class PasswordResetService
{

    protected $secret;
    protected $time_valid = 60*60*24; //24h

    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    /**
     * generate a token based on the global secret, the current timestamp, user password hash
     *
     * @param User $user
     * @return String
     */
    public function generateToken(User $user)
    {
        $secret = $this->secret;
        $pwHash = $user->getPasswordObject()->getHash();
        $time = time();

        $hash = hash('sha256', sprintf('%s%s%s', $secret, $pwHash, $time));
        $token = sprintf('%s_%s', $hash, $time);

        return $token;
    }


    /**
     * check a reset token
     *
     * @param User $user
     * @param      $token
     * @return bool
     */
    public function validateToken(User $user, $token)
    {
        if( strpos($token,'_') === false){
            return false;
        }

        list($hash, $time) = explode('_', $token);

        if( $time + $this->time_valid < time()){
            return false;
        }

        $secret = $this->secret;
        $pwHash = $user->getPasswordObject()->getHash();

        return hash('sha256', sprintf('%s%s%s', $secret, $pwHash, $time)) === $hash;
    }
}