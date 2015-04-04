<?php
namespace Cloud\LdapBundle\Entity


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
  
  
  public getServices() {
    
  }
  
  public getService($servicename) {
    
  }
}
