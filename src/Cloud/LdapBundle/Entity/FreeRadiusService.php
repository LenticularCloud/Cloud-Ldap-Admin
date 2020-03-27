<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Security\NtEncoder;
use Symfony\Component\Validator\Constraints as Assert;
use \InvalidArgumentException;
use Cloud\LdapBundle\Security\LdapPasswordEncoderInterface;
use Cloud\LdapBundle\Security\CryptEncoder;
use Cloud\LdapBundle\Schemas;

class FreeRadiusService extends AbstractService
{

    /**
     * passwords for this service
     *
     * @Assert\Valid()
     *
     * @var Password $passwords
     */
    protected $password = null;

    /**
     * @var boolean $masterPasswordEnabled
     */
    protected $masterPasswordEnabled = true;

    /**
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
    }

    public function getObjectClasses()
    {
        $classes = parent::getObjectClasses();
        $classes['sambasamaccount'] = Schemas\SambaSamAccount::class;

        return $classes;
    }

    public function afterAddObject($class)
    {
        if ($this->getObject(Schemas\SambaSamAccount::class) !== null && $this->getObject(Schemas\CloudService::class) !== null) {
            $attr = $this->getAttributes()->get('sambalmpassword');
            if ($attr->get() !== null) {
                $this->password = call_user_func($this->encoder.'::parsePassword', $attr);
                if ($this->isMasterPasswordEnabled()) {
                    $this->password->setMasterPassword(true);
                }
            }

            /*$this->passwords = [];
            foreach ($this->getObject(Schemas\ShadowAccount::class)->getUserPasswords() as $password) {
                $password = $this->encoder->parsePassword($password);
                $this->passwords[$password->getId()] = $password;
            }*/
        }
    }

    /**
     *
     * @return array<Password>
     */
    public function getPasswords()
    {
        if ($this->password === null) {
            return [];
        }

        return [$this->password];
    }

    /**
     * @param   string $passwordId
     * @return Password
     */
    public function getPassword($passwordId = null)
    {
        return $this->password;
    }

    /**
     *
     * @return Password
     */
    public function hasPassword($passwordId = null)
    {

        return $passwordId === null || $this->passwordObject->getId() == $passwordId;
    }

    /**
     *
     * @param Password $password
     * @return \Cloud\LdapBundle\Entity\Service
     */
    public function addPassword(Password $password)
    {
        //@TODO update to new schema
        if ($password->getEncoder() === $this->getEncoder()) {
            $attr = $this->getAttributes()->get('sambalmpassword');
            $attr->set($password->getAttribute()->get());
            $password->setAttribute($attr);
            $this->password = $password;

            return $this;
        }
        if ($password->getPasswordPlain() === null) {
            throw new \InvalidArgumentException("can't store false encoded password");
        }

        $password->setAttribute($this->getAttributes()->get('sambalmpassword'));
        call_user_func($this->getEncoder().'::encodePassword', $password);

        return $this;
    }

    /**
     *
     * @param Password $password
     * @return Service
     */
    public function removePassword(Password $password)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEncoder()
    {
        return NtEncoder::class;
    }


    protected function serviceEnabled()
    {
        parent::serviceEnabled();
        $this->getObject(Schemas\SambaSamAccount::class)->setSambaSID($this->getUser()->getUsername());
    }

    public function maxPasswords()
    {
        return 1;
    }
}
