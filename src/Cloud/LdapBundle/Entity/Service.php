<?php
namespace Cloud\LdapBundle\Entity;

use \Cloud\LdapBundle\Entity\Password;


class Service {
  
  /**
   * name of the service
   */
  private $name;
  
  /**
   * passwords for this service
   *  @var Array<Password> $passwords
   */
  private $passwords;
  
  
	/**
	 * @return unknown
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @param unknown $name
	 * @return \Cloud\LdapBundle\Entity\Service
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	
	/**
	 * return Array<Passwords>
	 */
	public function getPasswords() {
		return $this->passwords;
	}
	

	/**
	 * @param Password $password
	 * @return \Cloud\LdapBundle\Entity\Service
	 */
	public function addPassword( Password $password) {
		$this->passwords[$password->getId()] = $password;
		return $this;
	}
	
	/**
	 * 
	 * @param Password $password
	 */
	public function removePassword( Password $password ) {
		if(!isset($this->passwords[$password->getId()])) {
			throw \InvalidArgumentException("password not in the list");
		}
		unset($this->passwords[$password->getId()]);
		return $this;
	}
}
