<?php
namespace Cloud\LdapBundle\Schemas;


interface ShadowAccount
{
    public function getUid();
    public function setUid($uid);

    public function getUserPasswords();
    public function addUserPasswords($userPassword);
    public function removeUserPasswords($userPassword);

    public function getShadowLastChange();
    public function setShadowLastChange($shadowLastChange);

    public function getShadowExpire();
    public function setShadowExpire($shadowExpire);
}