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

//@TODO ldap_free_result refactor
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
   * 
   * @var String
   */
  private $domain;
  
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
    $this->services = $this->container->getParameter('services');
    $this->domain  	= $this->container->getParameter('domain');
    
    $this->ldap_resource=@ldap_connect($ldap_host,$ldap_port);
    if($this->ldap_resource===false) {
      throw new ConnectionErrorException();
    }
    
    $result=@ldap_set_option($this->ldap_resource, LDAP_OPT_PROTOCOL_VERSION, 3);
    if($result===false) {
    	throw new ConnectionErrorException('can\'t set to ldap v3: '.ldap_error ($this->ldap_resource));
    }
    $bind = @ldap_bind($this->ldap_resource,$bind_rdn, $bind_pw);
    if($bind===false) {
      throw new ConnectionErrorException('can\'t bind to ldap: '.ldap_error ($this->ldap_resource));
    }
  }
  
  /**
   * @TODO check each returnvalue and return a better exception on failuer
   */
  public function init() {
  	$dc=current(explode('.',$this->domain));

  	$data=array();
  	$data['dc']= $dc;
  	$data['ou']= $dc;
  	$data['objectClass']=array(
  			'organizationalUnit',
  			'dcObject'
  	);
  	ldap_add($this->ldap_resource,$this->base_dn,$data);
  	
  	$data=array();
  	$data['ou']='users';
  	$data['objectClass']=array(
  			'top',
  			'organizationalUnit'
  			);
  	ldap_add($this->ldap_resource,'ou=users,'.$this->base_dn,$data);

  	foreach($this->services as $service) {
  		$data=array();
  		$data['dc']= $service;
  		$data['ou']= $service;
  		$data['objectClass']=array(
  				'organizationalUnit',
  				'dcObject'
  		);
  		ldap_add($this->ldap_resource,'dc='.$service.','.$this->base_dn,$data);
  		 
  		$data=array();
  		$data['ou']='users';
  		$data['objectClass']=array(
  				'top',
  				'organizationalUnit'
  		);
  		ldap_add($this->ldap_resource,'ou=users,'.'dc='.$service.','.$this->base_dn,$data);
  	}
  }

  /**
   * get an array of all users
   * @return Array<User>
   * @throws LdapQueryException
   */
  public function getAllUsers(){
    $users=array();
    foreach($this->getAllUsernames() as $username){
      $users[]=$this->getUserByUsername($username);
    }
    
    return $users;
  }
  
  /**
   * get an array of all users
   * @return Array<User>
   * @throws LdapQueryException
   */
  public function getAllUsernames(){
  	

    $result = @ldap_list($this->ldap_resource,'ou=users,'.$this->base_dn,'(uid=*)',array('uid'));
    
    if($result===false) {
      throw new LdapQueryException('can not fetch userlist');
    }
    
    $info = ldap_get_entries($this->ldap_resource, $result);
    
    $users=array();
    for ($i=0; $i<$info["count"]; $i++) {
      $users[]=$info[$i]["uid"][0];
    }
    
    
    return $users;
  }
  
  /**
   * @throws UserNotFoundException
   */
  public function updateUser(User $user){
  
    $result = @ldap_mod_replace($this->ldap_resource, 'uid='.$user->getUsername().',ou=users,'.$this->base_dn , $this->userToLdapArray($user));
    if($result===false) {
    	throw new LdapQueryException('can not modify user');
    }
    foreach($this->services as $service) {
    	$result = @ldap_mod_replace($this->ldap_resource, 'uid='.$user->getUsername().',ou=users,dc='.$service.','.$this->base_dn , $this->userToLdapArray($user,$service));
    	if($result===false) {
    		throw new LdapQueryException('can not modify user\'s service '.$service);
    	}
    }
  }

  /**
   * creates a new user
   */
  public function createUser(User $user){
    $result = @ldap_add($this->ldap_resource, 'uid='.$user->getUsername().',ou=users,'.$this->base_dn , $this->userToLdapArray($user));
    if($result===false) {
    	throw new LdapQueryException('can not add user');
    }
    foreach($this->services as $service) {
    	$result = @ldap_add($this->ldap_resource, 'uid='.$user->getUsername().',ou=users,dc='.$service.','.$this->base_dn , $this->userToLdapArray($user,$service));
    	if($result===false) {
    		throw new LdapQueryException('can not add user to service '.$service);
    	}
    }
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
   * username has to be validated first
   * @throws UserNotFoundException
   * @return User
   */
  public function getUserByUsername($username){

  	$ri=ldap_search($this->ldap_resource, 'uid='.$username.',ou=users,'.$this->base_dn,'(objectClass=inetOrgPerson)',array('uid','userPassword'));
  	if($ri===false)  {
  		throw new LdapQueryException();
  	}
  	$result=ldap_first_entry($this->ldap_resource,$ri);
  	if($result===false) {
  		throw new UserNotFoundException();
  	}
  	$entity=ldap_get_attributes($this->ldap_resource, $result);
  	
  	$user=new User();
  	$user->setUsername($username);
  	$user->setPassword($this->parsePassword($entity['userPassword'][0]));
  	
  	ldap_free_result($ri);
  	foreach ($this->services as $service_name) {
  		$ri=ldap_read($this->ldap_resource, 'uid='.$username.',ou=users,dc='.$service_name.','.$this->base_dn,'(objectClass=inetOrgPerson)');
  		if($ri===false) {
  			throw new LdapQueryException();
  		}
  		$result=ldap_first_entry($this->ldap_resource,$ri);
  		if($result!==false) {
  			$entity=ldap_get_attributes($this->ldap_resource, $result);
	  		$service=new Service();
	  		$service->setName($service_name);
  			for($i=0;$i<$entity['userPassword']['count'];$i++) {
  				$password=$this->parsePassword($entity['userPassword'][$i]);
  				if($password->getId()!='main') { 
  					$service->addPassword($password);
  				}
  			}
  			$user->addService($service);
  		}
  		
  		ldap_free_result($ri);
  	}
  	
  	
  	return $user;
  }
  
  /**
   * 
   * @param string $password_hash
   * @return Password
   */
  private function parsePassword($password_hash) {
  	$password=new Password();
  	$password->setHash($password_hash);
  	$matches=null;
  	preg_match('#^{crypt}\$\d\$(rounds=\d+\$)?([0-9a-zAZ_-]+=)?[0-9a-zA-Z_-]+\$[^\$]*$#', $password_hash,$matches);
  	if($matches!=null) {
  		$password->setId(substr($matches[2],0,-1));
  	}
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
    throw new \BadFunctionCallException('not implemented yet');
  }
  
  /**
   * function to convert a user object into an array for ldap push
   * @param User $user
   * @param String $service Service name to get data
   */
  private function userToLdapArray(User $user,$service=null) {
    //@TODO passwordID
    $data=array();
    $data["cn"]             = $user->getUsername();
    $data["uid"]             = $user->getUsername();
    $data["objectClass"]    = array();
    $data["objectClass"][]  = "top";
    $data["objectClass"][]  = "inetOrgPerson";
    $data["objectClass"][]  = "posixAccount";
    $data["objectClass"][]  = "shadowAccount";

    $data["uid"]            = $user->getUsername();
    $data["homeDirectory"]  = "/var/vhome/".$user->getUsername();
    $data["givenName"]      = $user->getUsername();
    $data["sn"]             = $user->getUsername();
    $data["displayName"]    = $user->getUsername();
    $data["mail"]           = $user->getUsername()."@".$this->domain;
    $data['uidNumber'] 			= 1337; //@TODO: probably take a autoincrement id
    $data['gidNumber'] 			= 1337;
    $data["userPassword"]   = array();
    if($user->getPassword()->getHash()==null)
    	$this->cryptPassword($user->getPassword());
    $data["userPassword"][] = $user->getPassword()->getHash();
    if($service!==null && $user->getService($service)!=null) {
      foreach($user->getService($service)->getPasswords() as $password) {
    		if($password->getHash()==null)
    			$this->cryptPassword($password);
        $data["userPassword"][] = $password->getHash();
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
  	$rounds=60000; //incresed rounds for harder bruteforce
  	
  	
  	$salt="";
  	if($password->getId()!=null && $password->getId()!="") {
  		$salt=$this->getRandomeSalt(16-strlen($password->getId()));
  		$salt=$password->getId()."=".$salt;
  	} else {
    	$salt='main='.$this->getRandomeSalt();
  	}
		
    $hash=crypt($password->getPasswordPlain(),'$6$rounds='.$rounds.'$'.$salt.'$');
    $password->setHash('{crypt}'.$hash);
  }
  
  /**
   * generate randome string
   * @param number $length
   */
  private function getRandomeSalt($length=9) {
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
  
  /**
   * 
   */
  public function getServices(){
  	return $this->services;
  }
}
