<?php
namespace Cloud\LdapBundle\Util;

use Cloud\LdapBundle\Services\LdapClient;
use Cloud\LdapBundle\Entity\User;
use InvalidArgumentException;
use Cloud\LdapBundle\Security\LdapPasswordEncoderInterface;

class UserManipulator
{

    /**
     *
     * @var LdapClient $client
     */
    protected $client;
    protected $encoder;
    protected $baseDn;
    protected $validator;

    public function __construct(LdapClient $client,LdapPasswordEncoderInterface $encoder,$baseDn,$validator)
    {
        $this->client = $client;
        $this->baseDn = $baseDn;
        $this->encoder = $encoder;
        $this->validator = $validator;
    }

    public function create(User $user)
    {
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new InvalidArgumentException((string) $errors);
        }

        $userTransformer = new UserToLdapArrayTransformer($this->encoder);
        
        $this->client->replace('uid=' . $user->getUsername() . ',ou=Users,' . $this->baseDn, $userTransformer->transform($user));
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
            throw new InvalidArgumentException((string) $errors);
        }

        $userTransformer = new UserToLdapArrayTransformer($this->encoder);
        
        $this->client->replace('uid=' . $user->getUsername() . ',ou=users,' . $this->base_dn, $userTransformer->transform($user));
        
        /*foreach ($user->getServices() as $service) {
            $serviceTransformer ="TODO";
            $dn='uid=' . $user->getUsername() . ',ou=users,dc=' . $service->getName() . ',' . $this->base_dn;
            $this->client->replace($dn,
                 $serviceTransformer->transform($service));
        }*/
    }

    public function addRole(User $user, $role)
    {
        // @TODO
    }

    public function removeRole(User $user, $role)
    {
        // @TODO
    }
}