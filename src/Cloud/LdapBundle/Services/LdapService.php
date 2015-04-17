<?php

namespace Cloud\LdapBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Cloud\LdapBundle\Entity\User;
use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Exception\UserNotFoundException;
use Cloud\LdapBundle\Exception\ConnectionErrorException;
use Cloud\LdapBundle\Exception\LdapQueryException;
use Cloud\LdapBundle\Entity\Service;


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
  
  /**
   * 
   * @var String
   */
  private $base_dn;
  
  /**
   * @var array 
   */
  private $services;
  
  /**
   * 
   * @param ContainerInterface $container
   * @throws ConnectionErrorException
   */
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
    
    $ldap_host  		= $this->container->getParameter('ldap_server');
    $ldap_port  		= $this->container->getParameter('ldap_port');
    $bind_rdn   		= $this->container->getParameter('ldap_bind_rdn');
    $bind_pw    		= $this->container->getParameter('ldap_bind_pw');
    $this->base_dn 	= $this->container->getParameter('ldap_base_dn');
    $this->services 	= $this->container->getParameter('services');
    
    
    $this->ldap_resource=ldap_connect($ldap_host,$ldap_port);
    
    if($this->ldap_resource===false) {
      throw new ConnectionErrorException();
    }
    
    ldap_set_option($this->ldap_resource, LDAP_OPT_PROTOCOL_VERSION, 3);
    $bind = ldap_bind($this->ldap_resource,$bind_rdn, $bind_pw);
    
    if($bind===false) {
      throw new ConnectionErrorException('can\'t bind to ldap: '.ldap_error ($this->ldap_resource));
    }
  }

  /**
   * get an array of all users
   * @return Array<User>
   * @throws LdapQueryException
   */
  public function getAllUsers(){
    $users=array();
    foreach(getAllUsernames() as $username){
      $users[]=getUserByUsername($username);
    }
    
    return $users;
  }
  
  /**
   * get an array of all users
   * @return Array<User>
   * @throws LdapQueryException
   */
  public function getAllUsernames(){
  	
  	$ldap_base_dn="";//@TODO ...
  	

  	//@TODO use ldap_list
    $results = ldap_search($this->ldap_resource,$ldap_base_dn, "(cn=*)");
    
    if($results===false) {
      throw new LdapQueryException('can not fetch userlist');
    }
    
    $users=array();
    foreach($results as $result){
      $users[]=$result["uid"][0];
    }
    
    
    return $users;
  }
  
  /**
   * @throws UserNotFoundException
   */
  public function updateUser(User $user){
    
    $result = ldap_mod_replace($this->ldap_resource, "uid=".$user->getUsername().",ou=users,dc=milliways,dc=info", $this->userToLdapArray($user));

    //@TODO
    /*
    if($result) { ldap_error
    }*/
  }

  /**
   * creates a new user
   */
  public function createUser(User $user){
    
    $ldap_base="";//@TODO get ldap base
    
    $result = ldap_add($this->ldap_resource, 'uid='.$user->getUsername().',ou=users,'.$ldap_base , $this->userToLdapArray($user));
    //@TODO
    /*
     if($result) {
     }*/
  }
  
  /**
   * delete an user
   * @param User $user
   */
  public function deleteUser(User $user){
  	//@TODO .. ldap_delete
  	throw new \BadFunctionCallException('not implemented yet');
  }

  /**
   * search for user and return it
   * @throws UserNotFoundException
   * @return User
   */
  public function getUserByUsername($username){
  	
  	$ri=ldap_read($this->ldap_resource, 'uid='+$username+','+$this->getBaseDN(),'(objectClass=inetOrgPerson)');
  	if($ri===false)  {
  		throw new LdapQueryException();
  	}
  	$entity=ldap_first_entry($this->ldap_resource,$ri);
  	if($entity===false) {
  		throw new UserNotFoundException();
  	}
  	
  	$user=new User();
  	$user->setUsername($username);
  	
  	ldap_free_result($ri);
  	
  	foreach ($this->services as $service_name) {
  		$ri=ldap_read($this->ldap_resource, 'uid='+$username+','+$this->getBaseDN($service_name),'(objectClass=inetOrgPerson)');
  		if($ri===false) {
  			throw new LdapQueryException();
  		}
  		$entity=ldap_first_entry($this->ldap_resource,$ri);
  		if($entity===false) {
  			throw new LdapQueryException();
  		}
  		$service=new Service();
  		$service->setName($service_name);
  		
  		if(isset($entity['userPassword'])) {
  			foreach ($entity['userPassword'] as $password_hash) {
  				$service->addPassword();
  				
  			}
  		}else {
  			
  		}
  		
  		ldap_free_result($ri);
  	}
  	
  	
  	return $user;
  }
  
  /**
   * 
   * @param string $password_hash
   */
  private function parsePassword($password_hash) {
  	$password=new Password();
  	//@TODO not complete
  	preg_match('/^{crypt}\$()?$/', $subject,$matches);
  	
  	return $password;
  }
  
  /**
   * updates the users in the different services
   */
  public function updateServices(){
  	//@TODO ..
    throw new \BadFunctionCallException('not implemented yet');
  }
  
  /**
   * @TODO think about that
   */
  public function showServiceInconsistence(){
    //...
    //ldap_compare parsed with saved
  }
  
  /**
   * function to convert a user object into an array for ldap push
   * @param User $user
   * @param String $service Service name to get data
   */
  private function userToLdapArray(User $user,$service=null) {
    
    $domain="";//@TODO get domain
    //@TODO passwordID
    $data=array();
    $data["objectClass"]    = array();
    $data["objectClass"][]  = "top";
    $data["objectClass"][]  = "inetOrgPerson";
    $data["objectClass"][]  = "posixAccount";
    $data["objectClass"][]  = "shadowAccount";

    $data["uid"]            = $user->getUsername();
    $data["homeDirectory"]  = "/var/vhome/".$user->getUsername();
    /*$data["givenName"]      = $user->getUsername();
    $data["sn"]             = $this->getUsername();*/
    $data["displayName"]    = $this->getUsername();
    $data["cn"]             = $this->getUsername();
    $data["mail"]           = $this->getUsername."@".$domain;
    $data["userPassword"]   = array();
    $data["userPassword"][] = $this->cryptPassword($user->getPassword());
    if($service!==null) {
      foreach($user->getService($service)->getPasswords() as $password) {
        $data["userPassword"][] = $this->cryptPassword($password);
      }
    }
    $data["loginShell"]="/bin/false";

    
    return $data;
  }
  
  /**
   * function to create the password hash
   * @param Password $password valided password
   */
  private function cryptPassword(Password $password) {
  	$salt="";
  	if($password->get)
    $salt=getRandomeSalt();
    $hash=crypt($password->getPasswordPlain(),'$6$round=6000$'.$salt);
    return '{crypt}'.$hash;
  }
  
  /**
   * generate randome string
   * @param number $length
   */
  private function getRandomeSalt($length=10) {
    $chars="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

    $string="";
    $char_num=strlen($chars);
    for($i=0;$i<$length;$i++) {
      $string.=substr($chars,rand(0,$char_num-1),1);
    }

    return $string;
  }
  
  /**
   * 
   * @param string $service if null use
   */
  private function getBaseDN($service=null) {
  	return "ou=Users"+($service==null?"":"DN="+$service)+$this->base_dn;
  }
  
  /**
   * close current ldap connection
   */
  public function close(){
  	ldap_close($this->ldap_resource);
  }
}
