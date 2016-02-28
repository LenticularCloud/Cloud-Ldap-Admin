<?php
namespace Cloud\LdapBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Entity\Service;
use Cloud\LdapBundle\Entity\User;
use \InvalidArgumentException;

class UserTest extends WebTestCase
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
        $user = new User("test");
        $user->addPassword(new Password("123456","testID"));
        $this->assertEmpty($this->validator->validate($user));
        

        $user->addService(new Service("testService"));
        $this->assertEmpty($this->validator->validate($user));
    }

    public function testServiceInvalide()
    {
        $user = new User(null);
        $this->assertNotEmpty($this->validator->validate($user));


        $user = new User("test");
        //$user->setUsername("test");
        //$this->assertNotEmpty($this->validator->validate($user));
        
        $user->addPassword(new Password("123456","testID"));
        $this->assertEmpty($this->validator->validate($user));
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testException1(){
        $user=new User("testUser");
        $service=new Service("");
        $user->addService($service);
    }
    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testException2(){
        $user=new User("testUser");
        $user->addPassword(new Password("123456","tetID"));
        $user->addPassword(new Password("123456","tetID"));
    }
}
