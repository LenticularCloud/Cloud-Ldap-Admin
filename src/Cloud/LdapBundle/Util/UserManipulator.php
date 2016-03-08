<?php
namespace Cloud\LdapBundle\Util;

use Cloud\LdapBundle\Entity\AbstractService;
use Cloud\LdapBundle\Entity\MagicShaGroup;
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

        $this->client->add('cn=' . $user->getUsername() . ',ou=people,' . $this->baseDn, $transformer->transform($user));

        $group = new MagicShaGroup($user);
        $this->client->add('cn=' . $user->getCn() . ',ou=groups,' . $this->baseDn, $transformer->transform($group));

    }

    public function createUser($username)
    {
        $user = new User($username);

        foreach ($user->getObjectClasses() as $objectClass) {
            $user->addObject($objectClass);
        }
        $user->getObject(Schemas\InetOrgPerson::class)->setMail($username . '@' . $this->domain);
        $user->setHomeDirectory('/home/users/'.$username);
        $user->getObject(Schemas\InetOrgPerson::class)->setCn($username );
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

        $this->client->replace('cn=' . $user->getUsername() . ',ou=people,' . $this->baseDn, $transformer->transform($user));
    }

    public
    function delete(User $user)
    {

        $dn = 'cn=' . $user->getUsername() . ',ou=people,' . $this->baseDn;
        if ($this->client->isEntityExist($dn)) {
            $this->client->delete($dn);
        }
        foreach ($user->getServices() as $service) {

            $dn = 'cn=' . $user->getUsername() . ',ou=people,dc=' . $service->getName() . ',' . $this->baseDn;

            if ($this->client->isEntityExist($dn)) {
                $this->client->delete($dn);
            }
        }
    }
}