<?php
namespace Cloud\LdapBundle\Entity

class Password {

  /**
   * crypt format
   * last part of the salt is the id
   * @var String $hash
   */
  private $hash;

  /**
   * allow /a-Z0-9-/
   * @var String $id
   */
  private $id;

  /**
   * only used if pw changes
   * @var String $password_plain
   */
  private $password_plain=null;

} 
