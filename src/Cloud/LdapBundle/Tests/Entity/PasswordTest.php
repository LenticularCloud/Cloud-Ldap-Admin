<?php
namespace Cloud\LdapBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Cloud\LdapBundle\Entity\Password;

class PasswordTest extends WebTestCase
{

    private $validator;

    /**
     * @before
     */
    public function setUp()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->validator = $container->get('validator');
    }

    public function testPasswordValide()
    {
        $password = new Password();
        $password->setPasswordPlain("123456");
        
        $password->setId("valid");
        $this->assertEmpty($this->validator->validate($password));
        
        $password->setId("01");
        $this->assertEmpty($this->validator->validate($password));
        
        $password->setId("0101sfdgfd");
        $this->assertEmpty($this->validator->validate($password));
        
        $password->setId("aTd-_");
        $this->assertEmpty($this->validator->validate($password));
    }

    public function testPasswordInvalide()
    {
        $password = new Password();
        
        // no plain password and no hash
        $password->setId("valid");
        //$this->assertNotEmpty($this->validator->validate($password));
        
        $password->setPasswordPlain("123456");
        $password->setId("0");
        $this->assertNotEmpty($this->validator->validate($password));
        
        $password->setId(null);
        $this->assertNotEmpty($this->validator->validate($password));
        
        $password->setId(""); // empty id
        $this->assertNotEmpty($this->validator->validate($password));
        
        $password->setId("df."); // invalide char
        $this->assertNotEmpty($this->validator->validate($password));
        
        $password->setId("0101sfdgfda"); // to long
        $this->assertNotEmpty($this->validator->validate($password));
    }
}
