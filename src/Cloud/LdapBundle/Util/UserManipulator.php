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
    protected $bindDn;
    protected $bindPassword;

    public function __construct(LdapClient $client,LdapPasswordEncoderInterface $encoder,$validator,$baseDn,$bindDn,$bindPassword)
    {
        $this->client = $client;
        $this->baseDn = $baseDn;
        $this->encoder = $encoder;
        $this->validator = $validator;
        $this->bindDn = $bindDn;
        $this->bindPassword = $bindPassword;

        $this->client->bind($this->bindDn,$this->bindPassword);
    }

    public function create(User $user)
    {
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new InvalidArgumentException((string) $errors);
        }

        $userTransformer = new UserToLdapArrayTransformer();
        
        $this->client->replace('uid=' . $user->getUsername() . ',ou=users,' . $this->baseDn, $userTransformer->transform($user));
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

        $userTransformer = new UserToLdapArrayTransformer();

        $this->client->replace('uid=' . $user->getUsername() . ',ou=users,' . $this->baseDn, $userTransformer->transform($user));
        
        foreach ($user->getServices() as $service) {

            $dn='uid=' . $user->getUsername() . ',ou=users,dc=' . $service->getName() . ',' . $this->baseDn;
            
            if($service->isEnabled()) {
                $serviceTransformer =new ServiceToLdapArrayTransformer($service->getName());
                
                if($this->client->isEntityExist($dn)) {
                    $this->client->replace($dn,
                         $serviceTransformer->transform($service));
                }else {
                    $this->client->add($dn,
                         $serviceTransformer->transform($service));
                }
            }else {
                if($this->client->isEntityExist($dn)) {
                    $this->client->delete($dn);
                }
            }
        }
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