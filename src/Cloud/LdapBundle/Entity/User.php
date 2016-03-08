<?php
namespace Cloud\LdapBundle\Entity;

use Cloud\LdapBundle\Entity\Ldap\Attribute;
use Cloud\LdapBundle\Mapper as LDAP;
use Cloud\LdapBundle\Security\CryptEncoder;
use Cloud\LdapBundle\Security\NtEncoder;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Cloud\LdapBundle\Schemas;
use InvalidArgumentException;

class User extends AbstractUser implements AdvancedUserInterface
{

    private $username;

    /**
     * main passwords for this user
     *
     * @Assert\Valid(deep=true)
     * @Assert\NotBlank(message="You have to set a password.")
     *
     * @var Password $password
     */
    private $password = null;

    /**
     * main passwords for this user
     *
     * @Assert\Valid(deep=true)
     *
     * @var Password $password
     */
    private $ntPassword = null;


    /**
     *
     * @var boolean
     */
    private $enable = true;

    /**
     * @var string
     */
    private $passwordEncoder = CryptEncoder::class;

    public function __construct($username, array $roles = array(), $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true)
    {
        parent::__construct();
        $this->username = $username;
        $this->setEnable($enabled);
    }

    public function getObjectClasses()
    {
        return [
            'inetorgperson' => Schemas\InetOrgPerson::class,
            'posixaccount' => Schemas\PosixAccount::class,
            'qmailuser' => Schemas\QmailUser::class,
            'sambasamaccount'=> Schemas\SambaSamAccount::class,
        ];
    }

    public function afterAddObject($class)
    {
        switch ($class) {
            case Schemas\PosixAccount::class:
                $object = $this->getObject(Schemas\PosixAccount::class);
                if ($object->getUid() === null) {
                    $object->setUid($this->username);
                } else {
                    $this->username = $this->getObject(Schemas\PosixAccount::class)->getUid();
                }
                break;
            case Schemas\InetOrgPerson::class:
                $object = $this->getObject(Schemas\InetOrgPerson::class);
                if ($object->getSn() == null) {
                    $object->setSn($this->username);
                }
                if ($object->getCn() == null) {
                    $object->setCn($this->username);
                }

                $password = $this->getAttributes()->get('userpassword')->get(0);
                if ($password !== null) {
                    $password = CryptEncoder::parsePassword($password);
                    $this->password = $password;
                }
                break;
            case Schemas\SambaSamAccount::class:
                $object = $this->getObject(Schemas\SambaSamAccount::class);

                $password = $this->getAttributes()->get('sambalmpassword');
                if ($password !== null) {
                    $password = NtEncoder::parsePassword($password);
                    $this->ntPassword = $password;
                }

                $sid = $this->getAttributes()->get('sambasid');
                if($sid->get()===null) {
                    $sid->set('S-1-5-21-2919324557-891694127-41725'.$this->getUidNumber());
                }

                break;
        }
    }

    public function __sleep()
    {
        return array('username', 'enable');
    }

    public function getRoles()
    {
        return ["ROLE_USER"];
    }
    public function addRole($role)
    {
        return $this;
    }

    public function setRoles(array $roles)
    {
        return $this;
    }

    public function removeRole($role)
    {
        return $this;
    }

    /*
        public function getRoles()
        {
            if ($this->getObject(Schemas\LenticularUser::class) !== null) {
                return $this->getObject(Schemas\LenticularUser::class)->getAuthRoles();
            }
            return ["ROLE_USER"];
        }

        public function addRole($role)
        {
            $this->getObject(Schemas\LenticularUser::class)->addAuthRole($role);
            return $this;
        }

        public function setRoles(array $roles)
        {
            $this->getAttributes('userpassword')->clear();
            foreach ($roles as $role) {
                $this->getObject(Schemas\LenticularUser::class)->addAuthRole($role);
            }
            return $this;
        }

        public function removeRole($role)
        {
            $this->getObject(Schemas\LenticularUser::class)->removeAuthRole($role);
            return $this;
        }
    */
    public function getSalt()
    {
        return "";
    }

    public function eraseCredentials()
    {

    }


    /**
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=2,minMessage="Username must be at least {{ limit }} characters long")
     * @Assert\Regex("/^[a-zA-Z0-9_.-]+$/")
     *
     * @return String
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     *
     * @return Password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *
     * @param Password $password
     * @return \Cloud\LdapBundle\Entity\Service
     */
    public function setPassword(Password $password)
    {
        if ($password->getEncoder() !== CryptEncoder::class && $password->getPasswordPlain() === null) {
            throw new \InvalidArgumentException('can not add other password hash');
        }
        if ($password->getHash() === null) {
            CryptEncoder::encodePassword($password);
        }
        if($this->password !=null) {
            //switch attributes
            $attr = $this->password->getAttribute();
            $attr->set($password->getAttribute()->get());
        }else {
            $attr=$this->getAttributes()->get('userPassword');
        }
        $password->setAttribute($attr);
        $this->password = $password;
        return $this;
    }

    public function addPassword(Password $password) {
        return $this->setPassword($password);
    }

    /**
     *
     * @return Password
     */
    public function getNtPassword()
    {
        return $this->ntPassword;
    }

    /**
     *
     * @param Password $password
     * @return \Cloud\LdapBundle\Entity\Service
     */
    public function setNtPassword(Password $password)
    {
        if ($password->getEncoder() !== NtEncoder::class && $password->getPasswordPlain() === null) {
            throw new \InvalidArgumentException('can not add other password hash');
        }
        if ($password->getHash() === null) {
            NtEncoder::encodePassword($password);
        }
        if($this->ntPassword !=null) {
            //switch attributes
            $attr = $this->ntPassword->getAttribute();
            $attr->set($password->getAttribute()->get());
        }else {
            $attr=$this->getAttributes()->get('userpassword')->get(0);
            if($attr===null) {
                $attr = new Attribute();
                $this->getAttributes()->get('userpassword')->add($attr);
            }
        }
        $attr->set($password->getAttribute()->get());
        $password->setAttribute($attr);
        $this->ntPassword = $password;
        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function getEnable()
    {
        return $this->enable;
    }

    /**
     *
     * @param boolean $enable
     * @return User
     */
    public function setEnable($enable)
    {
        return $this;
    }

    public function getEmail()
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->getMail();
    }

    public function setEmail($email)
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->setMail($email);
    }

    public function getGivenName()
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->getGivenName();
    }

    public function setGivenName($givenName)
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->setGivenName($givenName);
    }

    public function getSureName()
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->getSn();
    }

    public function setSureName($sureName)
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->setSn($sureName);
    }

    public function getDisplayName()
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->getSn();
    }

    public function setDisplayName($displayName)
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->setSn($displayName);
    }

    public function getCn()
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->setCn();
    }

    public function setCn($cn)
    {
        return $this->getObject(Schemas\InetOrgPerson::class)->setCn($cn);
    }

    // ---- posix account ----

    public function getUidNumber()
    {
        return $this->getObject(Schemas\PosixAccount::class)->getUidNumber();
    }

    public function setUidNumber($uidNumber)
    {
        return $this->getObject(Schemas\PosixAccount::class)->setUidNumber($uidNumber);
    }

    public function getGidNumber()
    {
        return $this->getObject(Schemas\PosixAccount::class)->getGidNumber();
    }

    public function setGidNumber($gidNumber)
    {
        return $this->getObject(Schemas\PosixAccount::class)->setGidNumber($gidNumber);
    }

    public function getHomeDirectory()
    {
        return $this->getObject(Schemas\PosixAccount::class)->getHomeDirectory();
    }

    public function setHomeDirectory($homeDirectory)
    {
        return $this->getObject(Schemas\PosixAccount::class)->setHomeDirectory($homeDirectory);
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *false
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->enable;
    }
}
