<?php
namespace Cloud\LdapBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Entity\Service;
use Cloud\LdapBundle\Entity\User;

/**
 * 
 * @group ldap
 */
class LdapServiceTest extends WebTestCase
{
    public function testTODO()
    {
        $this->assertTrue(true);
    }

    /**
     * 
     * @var \Cloud\LdapBundle\Services\LdapService
     * /
    private $ldapService;
    
    /**
     * @before
     * /
    public function setUp()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->ldapService=$container->get("cloud.ldap");
    }
    
    public function testLdapServiceHasher()
    {
        $user=new User("testUser");
        $user->addPassword(new Password("testId","123456"));
        
        $service=new Service("mail");
        $service->addPassword(new Password("testId2","654321"));
        $user->addService($service);
        $this->ldapService->createUser($user);
        $this->assertContains("testUser",$this->ldapService->getAllUsernames());
        
        $user->addPassword(new Password("2pass","123456789"));
        $this->ldapService->updateUser($user);
        
        $user2=$this->ldapService->getUserByUsername($user->getUsername());
        $this->assertNotNull($user2);
        
        $this->assertNotNull($user2->getPassword("2pass"));
        
        $this->ldapService->deleteUser($user2);
        $this->assertNotContains("testUser",$this->ldapService->getAllUsernames());
    }
    
    
    public function testNotExistUser(){
        $this->assertNull($this->ldapService->getUserByUsername("notExistUsername"));

        $this->assertNull($this->ldapService->getUserByUsername("invalidUsername,dn=ö"));
    }*/
}
