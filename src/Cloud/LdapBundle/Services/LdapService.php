<?php

namespace Cloud\LdapBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Cloud\LdapBundle\Entity\User;

class LdapService
{
  
  /**
   * @var Ressorce
   */
  private $ldap_resource;
  
  public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
  {
    //$this->container = $container;
    
    $this->ldap_resource=ldap_connect($hostname,$port);
    
  }

  /**
   * get an array of all users
   * @return Array<User>
   */
  public function getAllUsers(){
    //@TODO
    return array();
  }
  
  /**
   * @throws UserNotFoundException
   */
  public function updateUser(User $user){
    
  }

  /**
   * creates a new user
   */
  public function createUser(){
    
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
      for
  }
  
  /**
   * @TODO think about that
   */
  public function showServiceIconsistence(){
    //...
  }
}
