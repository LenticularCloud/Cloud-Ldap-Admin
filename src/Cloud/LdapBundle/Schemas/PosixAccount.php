<?php
namespace Cloud\LdapBundle\Schemas;

/**
 * DESC 'Abstraction of an account with POSIX attributes'
 * SUP top AUXILIARY
 * MUST ( cn $ uid $ uidNumber $ gidNumber $ homeDirectory )
 * MAY ( userPassword $ loginShell $ gecos $ description ) )
 */
interface PosixAccount
{
    /**
     * @return string
     */
    public function getCn();

    /**
     * @param $cn   string
     * @return string
     */
    public function setCn($cn);


    public function getUid();

    public function setUid($uid);


    public function getUidNumber();

    public function setUidNumber($uidNumber);


    public function getGidNumber();

    public function setGidNumber($gidNumber);


    public function getHomeDirectory();

    public function setHomeDirectory($homeDirectory);


    public function getLoginShell();

    public function setLoginShell($loginShell);
}