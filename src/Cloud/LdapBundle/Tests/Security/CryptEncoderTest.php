<?php
namespace Cloud\LdapBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Entity\Service;
use Cloud\LdapBundle\Entity\User;

class CryptEncoderTest extends WebTestCase
{

    /**
     * 
     * @var \Cloud\LdapBundle\Security\PasswordEncoderInterface
     */
    private $encoder;
    
    /**
     * @before
     */
    public function setUp()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->encoder=$container->get("cloud.encoder.crypt");
    }
    
    public function testPasswordEncoder()
    {
        $password=new Password("testId");
        $password->setPasswordPlain("123456");
        
        $this->encoder->encodePassword($password);
        $this->assertGreaterThan(10,strlen($password->getHash()));
        
        $password->setPasswordPlain("654321");
        $this->assertFalse($this->encoder->isPasswordValid($password));
        
        $password->setPasswordPlain("123456");
        $this->assertTrue($this->encoder->isPasswordValid($password));
        
        $password2=$this->encoder->parsePassword($password->getHash());
        
        
        $this->assertEquals($password->getId(),$password2->getId());
        $this->assertEquals($password->getHash(),$password2->getHash());
    }
}
