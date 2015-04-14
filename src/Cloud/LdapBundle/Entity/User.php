<?php
namespace Cloud\LdapBundle\Entity;


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
  private $services;

  public function getUsername(){
    return $this->username;
  }

  public function setUsername($username){
    $this->username=$username;
    return $this;
  }

  public function getPassword(){
    return $this->password;
  }
  
  public function setPassword(Password $password){
    $this->password=$password;
    return $this;
  }
  
  /**
   * @return Array<Service>
   */
  public function getServices() {
    
  }
  
  /**
   * @return Service
   */
  public function getService($servicename) {
    
  }
}
