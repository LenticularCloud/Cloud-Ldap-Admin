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
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class Rehasher
{
    private $userManipulator;

    private $logger;

    public function __construct(Logger $logger, UserManipulator $userManipulator)
    {
        $this->logger = $logger;
        $this->userManipulator = $userManipulator;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        // Migrate the user to the new hashing algorithm if is using the legacy one
        if ($user instanceof User && $user->isLegacyPassword()) {
            $this->logger->info("Rehash password from user ".$user->getUsername());
            $this->userManipulator->update($user);
        }
    }
}