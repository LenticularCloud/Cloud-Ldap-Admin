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
   * 
   */
  public function init(){
  	$dc=explode(',dc=',$this->base_dn);
  	$dc=$dc[1];
  	$dc=substr($dc,3);
  	
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
  	

    $results = @ldap_list($this->ldap_resource,'ou='.$this->base_dn.', (uid=*)',array('uid'));
    
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
  	var_dump($this->userToLdapArray($user),'uid='.$user->getUsername().',ou=users,'.$this->base_dn);
    $result = ldap_add($this->ldap_resource, 'uid='.$user->getUsername().',ou=users,'.$this->base_dn , $this->userToLdapArray($user));
    if($result===false) {
    	throw new LdapQueryException('can not add user');
    }
    foreach($this->services as $service) {
  		var_dump($this->userToLdapArray($user,$service),'uid='.$user->getUsername().',ou=users,dc='.$service.','.$this->base_dn);
    	$result = ldap_add($this->ldap_resource, 'uid='.$user->getUsername().',ou=users,dc='.$service.','.$this->base_dn , $this->userToLdapArray($user,$service));
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
    	$salt='master='.$this->getRandomeSalt();
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
}
