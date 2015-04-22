<?php
namespace Cloud\LdapBundle\Entity;

use \Cloud\LdapBundle\Entity\Password;
use \Cloud\LdapBundle\Entity\Service;

class User {
  
  /**
   * @var String $username
   */
  private $username;
  
  /**
   * main password
   * @var Password $password
   */
  private $password;
  
  
  /**
   * assoziativ array with service info
   * @var AssoziativArray<Service>
   */
  private $services=array();
  
  /**
   * @TODO think about that
   * @var boolean 
   */
  private $enable;

  /**
   * @return String
   */
  public function getUsername(){
    return $this->username;
  }

  /**
   * 
   * @param unknown $username
   */
  public function setUsername($username){
    $this->username=$username;
    return $this;
  }

  /**
   * @return Password
   */
  public function getPassword(){
    return $this->password;
  }
  
  /**
   * 
   * @param Password $password
   */
  public function setPassword(Password $password){
    $this->password=$password;
    return $this;
  }
	

	/**
	 * @param Service $service
	 * @return \Cloud\LdapBundle\Entity\Service
	 */
	public function addService( Service $service) {
		$this->services[$service->getName()] = $service;
		return $this;
	}
	
	/**
	 * 
	 * @param Service $service
	 */
	public function removeService( Service $service ) {
		if(!isset($this->services[$service->getId()])) {
			throw \InvalidArgumentException("service not in the list");
		}
		unset($this->services[$service->getId()]);
		return $this;
	}
  

	/**
	 * @return AssoziativArray<Service>
	 */
	public function getServices() {
		return $this->services;
	}
	
  /**
   * @return Service
   */
  public function getService($name) {
    return isset($this->services[$name])?$this->services[$name]:null;
  }
  
  /**
   * @return boolean
   */
	public function getEnable() {
		return $this->enable;
	}
	
	/**
	 * 
	 * @param boolean $enable
	 */
	public function setEnable($enable) {
		$this->enable = $enable;
		return $this;
	}
	
  
}
