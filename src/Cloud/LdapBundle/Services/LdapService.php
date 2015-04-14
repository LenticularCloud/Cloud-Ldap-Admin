<?php

namespace Cloud\LdapBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Cloud\LdapBundle\Entity\User;
use Cloud\LdapBundle\Exception\UserNotFoundException;
use Cloud\LdapBundle\Exception\ConnectionErrorException;
use Cloud\LdapBundle\Exception\LdapQueryException;


class LdapService
{
  
  /**
   * @var Ressorce $ldap_resource
   */
  private $ldap_resource;
  
  /**
   * @var ContainerInterface $container
   */
  private $container;
  
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
    
    $ldap_host  =$this->container->getParameter('ldap_server');
    $ldap_port  =$this->container->getParameter('ldap_port');
    $bind_rdn   =$this->container->getParameter('ldap_bind_rdn');
    $bind_pw    =$this->container->getParameter('ldap_bind_pw');
    
    $this->ldap_resource=ldap_connect($ldap_host,$ldap_port);
    
    if($this->ldap_resource===false) {
      throw new ConnectionErrorException();
    }
    
    ldap_set_option($this->ldap_resource, LDAP_OPT_PROTOCOL_VERSION, 3);
    $bind = ldap_bind($this->ldap_resource,$bind_rdn, $bind_pw);
    
    if($bind===false) {
      throw new ConnectionErrorException();
        die('can\'t bind to ldap: '.ldap_error ($ldapconn));
    }
  }

  /**
   * get an array of all users
   * @return Array<User>
   * @throws LdapQueryException
   */
  public function getAllUsers(){
    
    $users=array();
    foreach(getAllUsernames() as $result){
      $users[]=getUserByUsername($user["uid"][0]);
    }
    
    
    return $users;
  }
  
  /**
   * get an array of all users
   * @return Array<User>
   * @throws LdapQueryException
   */
  public function getAllUsernames(){
    $results = ldap_search($this->ldap_resource,$ldap['base_dn'], "(cn=*)");
    
    if($result===false) {
      throw new LdapQueryException('can not fetch userlist');
    }
    
    $users=array();
    foreach($results as $result){
      $users[]=$user["uid"][0];
    }
    
    
    return $users;
  }
  
  /**
   * @throws UserNotFoundException
   */
  public function updateUser(User $user){
    
    $result = ldap_mod_replace($this->ldap_resource, "uid=$username,ou=users,dc=milliways,dc=info", $this->userToLdapArray($user));
  }

  /**
   * creates a new user
   */
  public function createUser(User $user){
    
    $ldap_base="";//@TODO get ldap base
    
    $add = ldap_add($this->ldap_resource, 'uid='.$user->getUsername().',ou=users,'.$ldap_base , $this->userToLdapArray($user));
    
  }

  /**
   * search for user and return it
   * @throws UserNotFoundException
   * @return User
   */
  public function getUserByUsername($username){
    
  }
  
  /**
   * updates the users in the different services
   */
  public function updateServices(){
      
  }
  
  /**
   * @TODO think about that
   */
  public function showServiceInconsistence(){
    //...
  }
  
  /**
   * function to convert a user object into an array for ldap push
   * @param User $user
   * @param String $service Service name to get data
   */
  private function userToLdapArray(User $user,$service=null) {
    
    $domain="";//@TODO get domain
    
    $data=array();
    $data["objectClass"]    = array();
    $data["objectClass"][]  = "top";
    $data["objectClass"][]  = "inetOrgPerson";
    $data["objectClass"][]  = "posixAccount";
    $data["objectClass"][]  = "shadowAccount";

    $data["uid"]            = $user->getUsername();
    $data["homeDirectory"]  = "/var/vhome/".$username;
    $data["givenName"]      = $user->getUsername();
    $data["sn"]             = $this->getUsername();
    $data["displayName"]    = $this->getUsername();
    $data["cn"]             = $this->getUsername();
    $data["mail"]           = $this->getUsername."@".$domain;
    $data["userPassword"]   = array();
    $data["userPassword"][] = $this->cryptPassword($user->getPassword());
    if($service!==null) {
      foreach($user->getService($service)->getPasswords() as $password) {
        $data["userPassword"][] = $this->cryptPassword($user->getPassword());
      }
    }

    //$data["uidnumber"]="5000";
    //$data["gidNumber"]="5000";
    $data["loginShell"]="/bin/false";

    
    return $data;
  }
  
  private function cryptPassword(Password $password) {
    
    
    if($password->getPasswordPlain()!==null) {
      $salt=getRandomeSalt();
      $hash=crypt($password->getPasswordPlain(),'$6$round=6000$'.$salt);
    }
    
    
    return '{crypt}'.$password->getHash();
  }
  
  private function getRandomeSalt($length=10) {
    $chars="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

    $string="";
    $char_num=strlen($chars);
    for($i=0;$i<$length;$i++) {
      $string.=substr($chars,rand(0,$char_num-1),1);
    }

    return $string;
  }
}
