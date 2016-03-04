<?php
namespace Cloud\LdapBundle\Util;

use Cloud\LdapBundle\Entity\AbstractService;
use Cloud\LdapBundle\Services\LdapClient;
use Cloud\LdapBundle\Entity\User;
use InvalidArgumentException;
use Cloud\LdapBundle\Security\LdapPasswordEncoderInterface;
use Cloud\LdapBundle\Schemas;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManipulator
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

    public function __construct(LdapClient $client, ValidatorInterface $validator, $baseDn, $bindDn, $bindPassword, $domain)
    {
        $this->client = $client;
        $this->baseDn = $baseDn;
        $this->validator = $validator;
        $this->bindDn = $bindDn;
        $this->bindPassword = $bindPassword;
        $this->domain = $domain;

        $this->client->bind($this->bindDn, $this->bindPassword);
    }

    public function create(User $user)
    {
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new InvalidArgumentException((string)$errors);
        }
        $transformer = new LdapArrayToObjectTransformer();

        $this->client->add('uid=' . $user->getUsername() . ',ou=users,' . $this->baseDn, $transformer->transform($user));
        foreach ($user->getServices() as $service) {

            $dn = 'uid=' . $user->getUsername() . ',ou=users,dc=' . $service->getName() . ',' . $this->baseDn;
            if ($service->isEnabled()) {
                $this->client->add($dn,
                    $transformer->transform($service));
            }
        }
    }

    public function createUser($username)
    {
        $user = new User($username);

        foreach ($user->getObjectClasses() as $objectClass) {
            $user->addObject($objectClass);
        }
        $user->getObject(Schemas\InetOrgPerson::class)->setMail($username . '@' . $this->domain);
        $user->addRole('ROLE_USER');
        return $user;
    }

    public function activate(User $user, $service = null)
    {
        // @TODO
    }

    public function deactivate(User $user, $service = null)
    {
        // @TODO
    }

    public function update(User $user)
    {
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new InvalidArgumentException((string)$errors);
        }

        // validate ldap schemas
        foreach ($user->getObjects() as $object) {
            $errors = $this->validator->validate($object);
            if (count($errors) > 0) {
                throw new InvalidArgumentException($this->getUsername() . '(User):' . (string)$errors);
            }
        }

        $transformer = new LdapArrayToObjectTransformer(null);

        $this->client->replace('uid=' . $user->getUsername() . ',ou=users,' . $this->baseDn, $transformer->transform($user));

        foreach ($user->getServices() as $service) {

            $dn = 'uid=' . $user->getUsername() . ',ou=users,dc=' . $service->getName() . ',' . $this->baseDn;
            if ($service->isEnabled()) {
                // validate ldap schemas
                foreach ($service->getObjects() as $object) {
                    $errors = $this->validator->validate($object);
                    if (count($errors) > 0) {
                        throw new InvalidArgumentException($service->getName() . "(Service): " . (string)$errors);
                    }
                }
                if ($this->client->isEntityExist($dn)) {
                    $this->client->replace($dn,
                        $transformer->transform($service));
                } else {
                    $this->client->add($dn,
                        $transformer->transform($service));
                }

                //add groups
                foreach ($service->getGroups() as $group) {
                    $dnGroup = 'uid=' . $user->getUsername() . ',ou=groups,dc=' . $service->getName() . ',' . $this->baseDn;
                    $errors = $this->validator->validate($group);
                    if (count($errors) > 0) {
                        throw new InvalidArgumentException($group->getName() . "(Group): " . (string)$errors);
                    }
                    if ($group->isEnabled()) {

                        if ($this->client->isEntityExist($dnGroup)) {
                            $this->client->replace($dnGroup,
                                $transformer->transform($service));
                        } else {
                            $this->client->add($dnGroup,
                                $transformer->transform($service));
                        }
                    } else {
                        if ($this->client->isEntityExist($dnGroup)) {
                            $this->client->delete($dnGroup);
                        }
                    }
                }
            } else { // !$service->isEnabled()
                if ($this->client->isEntityExist($dn)) {
                    $this->client->delete($dn);
                }
            }
        }
    }

    public
    function delete(User $user)
    {

        $dn = 'uid=' . $user->getUsername() . ',ou=users,' . $this->baseDn;
        if ($this->client->isEntityExist($dn)) {
            $this->client->delete($dn);
        }
        foreach ($user->getServices() as $service) {

            $dn = 'uid=' . $user->getUsername() . ',ou=users,dc=' . $service->getName() . ',' . $this->baseDn;

            if ($this->client->isEntityExist($dn)) {
                $this->client->delete($dn);
            }
        }
    }
}