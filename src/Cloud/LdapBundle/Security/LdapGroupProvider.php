<?php
namespace Cloud\LdapBundle\Security;


use Cloud\LdapBundle\Entity\AbstractGroup;
use Cloud\LdapBundle\Entity\Group;
use Cloud\LdapBundle\Services\LdapClient;
use Cloud\LdapBundle\Util\LdapArrayToObjectTransformer;
use Doctrine\Common\Annotations\Reader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Cloud\LdapBundle\Entity\User;
use Symfony\Component\Ldap\Exception\ConnectionException;

class LdapGroupProvider
{

    protected $logger;
    protected $ldap;
    protected $baseDn;
    protected $searchDn;
    protected $searchPassword;

    /**
     * @var Reader
     */
    protected $reader;


    public function __construct(
        LoggerInterface $logger,
        LdapClient $ldap,
        $baseDn,
        $searchDn,
        $searchPassword,
        Reader $reader
    ) {
        $this->logger = $logger;
        $this->ldap = $ldap;
        $this->baseDn = $baseDn;
        $this->searchDn = $searchDn;
        $this->searchPassword = $searchPassword;
        $this->reader = $reader;

        $this->ldap->bind($this->searchDn, $this->searchPassword);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        $groupClass = AbstractGroup::class;

        return $groupClass === $class || is_subclass_of($class, $groupClass);
    }

    /**
     * @return Group[]
     */
    public function loadGroupByUser(User $user)
    {
        $groups=[];
        $groupnames = $this->ldap->getEntitynames("ou=SecurityGroups,".$this->baseDn,'cn',sprintf('(member=%s)',$user->getDn()));
        foreach ($groupnames as $groupname) {
            $group = $this->loadGroupByName($groupname);
            $groups[]=$group;
        }
        return $groups;
    }


    /**
     * @return Group
     */
    public function loadGroupByName($groupname)
    {
        $groupname = $this->ldap->escape($groupname, '', LDAP_ESCAPE_FILTER);
        $query = sprintf('cn=%s', $groupname);
        $filter = array(
            'createTimestamp',
            'modifyTimestamp',
            '*',
        );

        $dn = "ou=SecurityGroups,".$this->baseDn;
        try {
            $search = $this->ldap->find($dn, $query, $filter);
        } catch (ConnectionException $e) {
            $this->logger->error('try search for ldap group ', $e);
            throw new UsernameNotFoundException(sprintf('Group "%s" not found.', $groupname), 0, $e);
        }

        if (!$search) {
            throw new UsernameNotFoundException(sprintf('Group "%s" not found.', $groupname));
        }

        if ($search['count'] > 1) {
            $this->logger->alert(sprintf('more than one group found: query: %s result: %s', $query, $search));
            throw new UsernameNotFoundException('More than one group found');
        }

        $transformer = new LdapArrayToObjectTransformer($this->reader);

        return $transformer->reverseTransform($search[0], new Group($groupname), sprintf('cn=%s,%s', $groupname,$dn));
    }

    /**
     * get an array of all users
     *
     * @return User[]
     * @throws LdapQueryException
     */
    public function getGroups()
    {
        $groups = array();
        foreach ($this->getGroupnames() as $username) {
            $groups[] = $this->loadGroupByName($username);
        }
        return $groups;
    }

    public function getGroupnames()
    {
        $usernames = $this->ldap->getEntitynames("ou=SecurityGroups,".$this->baseDn,'cn');
        sort($usernames);

        return $usernames;
    }

    /**
     */
    public function refresh(AbstractGroup $group)
    {
        if (!$group instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($group)));
        }
        $_group = $this->loadGroupByName($group->getUsername());

        return $_group;
    }
}