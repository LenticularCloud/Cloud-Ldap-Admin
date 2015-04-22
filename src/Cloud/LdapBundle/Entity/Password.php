<?php
namespace Cloud\LdapBundle\Entity;

class Password {

  /**
   * crypt format
   * last part of the salt is the id
   * @var String $hash
   */
  private $hash;

  /**
   * allow /a-Z0-9_-/
   * @var String $id
   */
  private $id;

  /**
   * only used if pw changes
   * @var String $password_plain
   */
  private $password_plain=null;
  
  public function __construct($password_plain=null,$id=null) {
    $this->password_plain=$password_plain;
    $this->id=$id;
  }
	
	/**
	 *
	 * @return the String
	 */
	public function getHash() {
		return $this->hash;
	}
	
	/**
	 *
	 * @param
	 *        	$hash
	 */
	public function setHash($hash) {
		$this->hash = $hash;
		return $this;
	}
	
	/**
	 *
	 * @return the String
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * unique
	 * allow /(a-Z0-9_-){1,10}/
	 * @param String $id
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	
	/**
	 * 
	 * @return the String
	 */
	public function getPasswordPlain() {
		return $this->password_plain;
	}
	
	/**
	 *
	 * @param $password_plain
	 */
	public function setPasswordPlain($password_plain) {
		$this->password_plain = $password_plain;
		return $this;
	}
	

} 
