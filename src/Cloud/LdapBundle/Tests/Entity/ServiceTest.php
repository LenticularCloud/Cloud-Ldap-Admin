<?php
namespace Cloud\LdapBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Entity\Service;

class ServiceTest extends WebTestCase
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

    public function testServiceValide()
    {
        $service = new Service("test");
        $this->assertEmpty($this->validator->validate($service));
        
        $validPassword = new Password();
        $validPassword->setId("valid");
        $validPassword->setPasswordPlain("123456");
        
        $service->addPassword($validPassword);
        $this->assertEmpty($this->validator->validate($service));
        
        $service->removePassword($validPassword);
        $this->assertEmpty($this->validator->validate($service));
        $this->assertEmpty($service->getPasswords());
    }

    public function testServiceInvalide()
    {
        $service = new Service("");
        $this->assertNotEmpty($this->validator->validate($service));
        $service = new Service("testService");
        
        $invalidPassword = new Password();
        $invalidPassword->setId("invalidö");
        $invalidPassword->setPasswordPlain("123456");
        
        $service->addPassword($invalidPassword);
        $this->assertNotEmpty($this->validator->validate($service));
    }
}
