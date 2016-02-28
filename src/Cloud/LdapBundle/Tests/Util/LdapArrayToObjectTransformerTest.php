<?php
namespace Cloud\LdapBundle\Test\Util;

use Cloud\LdapBundle\Entity\User;
use Cloud\LdapBundle\Entity\Service;
use Cloud\LdapBundle\Schemas\PosixAccount;
use Cloud\LdapBundle\Util\LdapArrayToObjectTransformer;
use Doctrine\Common\Annotations\AnnotationReader;

class LdapArrayToObjectTransformerTest extends \PHPUnit_Framework_TestCase
{

    public function testTransform ()
    {
        /*new
        $transformer=new UserToLdapArrayTransformer();

        $user=new User("testuser");
        $pw=new Password();
        $hash='{crypt}$6$rounds=60000$testAIbklOGzurN6$FOl9R8bgP4GVtXKeKTil2uMpJfSlEfcBM.1JJWKnrUgdA8Hxve4qONQLh9TprJviNb9TpeoMZdGGt8YnPu/uv.';
        $pw->setHash($hash);
        $pw->setId("test");
        $user->addPassword($pw);

        $array=$transformer->transform($user);

        $this->assertEquals("testuser", $array["uid"]);

        $this->assertContains($hash, $array["userPassword"]);
        //@TODO test other filds to
        */

    }

    public function testReverseTransform()
    {
        $username="testuser";
        $data = array();
        $data["cn"] = $username;
        $data["uid"] = $username;
        $data["objectClass"] = array();
        $data["objectClass"][] = "inetOrgPerson";
        $data["objectClass"][] = "posixAccount";
        $data["objectClass"][] = "shadowAccount";

        $data["uid"] = $username;
        $data["homeDirectory"] = "/var/vhome/" . $username;
        $data["givenName"] = $username;
        $data["sn"] = $username;
        $data["displayName"] = $username;
        $data["mail"] = $username . "@example.com";
        $data['uidNumber'] = 1337; // @TODO: probably take a autoincrement id
        $data['gidNumber'] = 1337;
        $data["loginShell"] = "/bin/false";
        $data['userPassword']=array();
        $data['userPassword']['0'] = '{crypt}$6$rounds=60000$test=IbklOGzurN6$FOl9R8bgP4GVtXKeKTil2uMpJfSlEfcBM.1JJWKnrUgdA8Hxve4qONQLh9TprJviNb9TpeoMZdGGt8YnPu/uv.';
        $data['userPassword']['count']=1;


        $reader = new AnnotationReader();
        $transformer=new LdapArrayToObjectTransformer($reader);

        $entity=$transformer->reverseTransform($data);

        $this->assertNotNull($entity->getObject(PosixAccount::class));
        $this->assertEquals($entity->getObject(PosixAccount::class)->getUid(), $username);
        /*$this->assertCount(1, $user->getPasswords());
        $this->assertNotNull($user->getPassword("test"));
        $this->assertEquals($user->getPassword("test")->getHash(),$data["userPassword"]['0']);*/

    }
}
