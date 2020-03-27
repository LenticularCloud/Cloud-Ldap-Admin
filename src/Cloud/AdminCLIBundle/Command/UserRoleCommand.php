<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Cloud\LdapBundle\Entity\User;
use Cloud\LdapBundle\Entity\Password;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserRoleCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('cloud:userrole')
            ->setDescription('lists all the current users')
            ->addArgument('username', InputArgument::OPTIONAL, null)
            ->addArgument('role', InputArgument::OPTIONAL, null)
            ->addOption('add', '-a', InputOption::VALUE_NONE, 'add an user role')
            ->addOption('delete', '-d', InputOption::VALUE_NONE, 'deletes an user role')
            ->addOption('json', '-j', InputOption::VALUE_NONE, 'Output as JSON')
            ->setHelp('outputs a list of users as list or as json');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        try {
            $this->getContainer()->get('cloud.ldap.userprovider');
        } catch (\Exception $e) {
            $output->writeln("<error>Can't connect to database</error>");

            return 255;
        }

        // check parameters
        if ($input->getOption('add') && $input->getOption('delete')) {
            $output->writeln("<error>can't add and delete</error>");

            return 1;
        }

        // read username
        $username = $this->_getUsername($input, $output);

        try {
            $user = $this->getContainer()
                ->get('cloud.ldap.userprovider')
                ->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            $output->writeln("<error>can't find user</error>");
        }

        // if no option is set show user rights
        if (!$input->getOption('add') && !$input->getOption('delete')) {
            if ($input->getOption('json')) {
                $output->writeln(json_encode($user->getRoles()));
            } else {
                foreach ($user->getRoles() as $role) {
                    $output->writeln($role);
                }
            }

            return 0;
        }


        // read role
        $role = null;
        if ($input->getArgument('role')) {
            $role = $input->getArgument('role');
        } else {
            $question = new Question('Please a role (eg. ROLE_USER):');
            $role = $helper->ask($input, $output, $question);
        }

        if ($input->getOption('delete')) {
            $user->removeRole($role);
            $this->getContainer()
                ->get('cloud.ldap.util.usermanipulator')
                ->update($user);

            return 0;
        }

        if ($input->getOption('add')) {
            $user->addRole($role);
            $this->getContainer()
                ->get('cloud.ldap.util.usermanipulator')
                ->update($user);

            return 0;
        }

        return 0;
    }

    /**
     * request username, ether by argument or by user input
     * 
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return mixed|null
     */
    protected function _getUsername(InputInterface $input, OutputInterface $output)
    {
        $username = null;
        $helper = $this->getHelper('question');
        if ($input->getArgument('username') !== null) {
            $username = $input->getArgument('username');
        } else {
            $question = new Question('Please enter the name of the User:');
            $question->setAutocompleterValues($this->getContainer()
                ->get('cloud.ldap.userprovider')
                ->getUsernames());
            $username = $helper->ask($input, $output, $question);
        }

        return $username;
    }
}
