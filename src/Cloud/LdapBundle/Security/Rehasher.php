<?php
/**
 * Created by PhpStorm.
 * User: tuxcoder
 * Date: 22.08.18
 * Time: 21:55
 */

namespace Cloud\LdapBundle\Security;


use Cloud\LdapBundle\Entity\User;
use Cloud\LdapBundle\Util\UserManipulator;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class Rehasher
{
    private $userManipulator;

    public function __construct(UserManipulator $userManipulator)
    {
        $this->userManipulator = $userManipulator;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        // Migrate the user to the new hashing algorithm if is using the legacy one
        if ($user instanceof User && $user->isLegacyPassword()) {
            $this->userManipulator->update($user);
        }
    }
}