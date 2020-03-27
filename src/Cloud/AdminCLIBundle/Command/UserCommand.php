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

class UserCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('cloud:user')
            ->setDescription('lists all the current users')
            ->addArgument('username', InputArgument::OPTIONAL, null)
            ->addArgument('password', InputArgument::OPTIONAL, null)
            ->addOption('add', '-a', InputOption::VALUE_NONE, 'add an user')
            ->addOption('delete', '-d', InputOption::VALUE_NONE, 'deletes an user')
            ->addOption('force', '-f', InputOption::VALUE_NONE, 'force deletes an user without asking')
            ->addOption('json', '-j', InputOption::VALUE_NONE, 'Output as JSON')
            ->setHelp('outputs a list of users as list or as json');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

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

        // if no option is set show user list
        if (!$input->getOption('add') && !$input->getOption('delete')) {
            return $this->_list($input, $output);
        }

        // read username

        if ($input->getOption('delete')) {
            return $this->_delete($input, $output);
        }

        if ($input->getOption('add')) {
            return $this->_add($input, $output);
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
            if ($input->getOption('delete')) {
                $question->setAutocompleterValues($this->getContainer()
                    ->get('cloud.ldap.userprovider')
                    ->getUsernames());
            }
            $username = $helper->ask($input, $output, $question);
        }

        return $username;
    }

    private function _list(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('json')) {
            $output->writeln(json_encode($this->getContainer()
                ->get('cloud.ldap.userprovider')
                ->getUsernames()));
        } else {
            foreach ($this->getContainer()
                         ->get('cloud.ldap.userprovider')
                         ->getUsernames() as $username) {
                $output->writeln($username);
            }
        }


        return 0;

    }

    private function _delete(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        try {
            $user = $this->getContainer()
                ->get('cloud.ldap.userprovider')
                ->loadUserByUsername($this->_getUsername($input,$output));
        } catch (UsernameNotFoundException $e) {
            $output->writeln("<error>can't find user</error>");
            return 1;
        }

        if (!$input->getOption('force')) {
            $question = new ConfirmationQuestion('You realy whant to delete this user? [y/N]:', false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<error>canceled by user, if you use a script use \'-f\' to force delete</error>');

                return 1;
            }
        }

        $this->getContainer()
            ->get('cloud.ldap.util.usermanipulator')
            ->delete($user);

        return 0;
    }

    private function _add(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $username = $this->_getUsername($input,$output);
        try {
            $user = $this->getContainer()
                ->get('cloud.ldap.userprovider')
                ->loadUserByUsername($username);
            $output->writeln("<error>username allready taken</error>");

            return 1;
        } catch (UsernameNotFoundException $e) {
        }

        // read password
        $password = null;
        if ($input->getArgument('password')) {
            $password = $input->getArgument('password');
        } else {
            $question = new Question('Please enter password:');
            $question->setHidden(true);
            $password = $helper->ask($input, $output, $question);
        }

        $user = $this->getContainer()->get('cloud.ldap.util.usermanipulator')->createUserObject($username,
            new Password('default', $password, true));

        $this->getContainer()
            ->get('cloud.ldap.util.usermanipulator')
            ->create($user);

        return 0;

    }
}
