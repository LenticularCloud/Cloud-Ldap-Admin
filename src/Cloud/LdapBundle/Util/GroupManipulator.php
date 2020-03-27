<?php
namespace Cloud\LdapBundle\Util;

use Cloud\LdapBundle\Entity\Group;
use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Services\LdapClient;
use Cloud\LdapBundle\Entity\User;
use InvalidArgumentException;
use Cloud\LdapBundle\Security\LdapPasswordEncoderInterface;
use Cloud\LdapBundle\Schemas;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupManipulator
{

    /**
     *
     * @var LdapClient $client
     */
    protected $client;
    protected $baseDn;
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    protected $bindDn;
    protected $bindPassword;
    protected $domain;

    protected $services;

    /**
     * UserManipulator constructor.
     * @param LdapClient         $client    client instance
     * @param ValidatorInterface $validator valicator instance
     * @param string             $baseDn
     * @param string             $bindDn
     * @param string             $bindPassword
     * @param array              $services  service config
     * @param string             $domain
     */
    public function __construct(
        LdapClient $client,
        ValidatorInterface $validator,
        $baseDn,
        $bindDn,
        $bindPassword,
        $services,
        $domain
    ) {
        $this->client = $client;
        $this->baseDn = $baseDn;
        $this->validator = $validator;
        $this->bindDn = $bindDn;
        $this->bindPassword = $bindPassword;
        $this->domain = $domain;
        $this->services = $services;

        $this->client->bind($this->bindDn, $this->bindPassword);
    }

    public function create(Group $group)
    {
        $errors = $this->validator->validate($group);
        if (count($errors) > 0) {
            throw new InvalidArgumentException((string) $errors);
        }
        $transformer = new LdapArrayToObjectTransformer();

        $this->client->add('cn='.$group->getName().',ou=SecurityGroups,'.$this->baseDn, $transformer->transform($group));
        //@TODO Services
    }

    public function createGroupObject($name)
    {
        $name = strtolower($name); //search name can only be lower case
        $group = new User($name);

        foreach ($group->getObjectClasses() as $objectClass) {
            $group->addObject($objectClass);
        }
        //@TODO services

        return $group;
    }

    public function activate(User $user, $service = null)
    {
        // @TODO
    }

    public function deactivate(User $user, $service = null)
    {
        // @TODO
    }

    public function update(Group $group)
    {
        $errors = $this->validator->validate($group);
        if (count($errors) > 0) {
            throw new InvalidArgumentException((string) $errors);
        }


        // validate ldap schemas
        foreach ($group->getObjects() as $object) {
            $errors = $this->validator->validate($object);
            if (count($errors) > 0) {
                throw new InvalidArgumentException($group->getName().'(Group):'.(string) $errors);
            }
        }

        $transformer = new LdapArrayToObjectTransformer(null);

        $this->client->replace('cn='.$group->getName().',ou=SecurityGroups,'.$this->baseDn, $transformer->transform($group));

        //@TODO services
    }

    public function delete(Group $group)
    {

        $dn = 'cn='.$group->getName().',ou=SecurityGroups,'.$this->baseDn;
        if ($this->client->isEntityExist($dn)) {
            $this->client->delete($dn);
        }
        //@TODO services
    }
}