<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class PasswdCommand extends ContainerAwareCommand
{

    /**
     *
     * @var string
     */
    private $username;

    /**
     *
     * @var string
     */
    private $service;

    /**
     *
     * @var string
     */
    private $passwordId;

    /**
     *
     * @var string
     */
    private $password;

    /**
     *
     * @var \Cloud\LdapBundle\Entity\User
     */
    private $user;

    protected function configure()
    {
        $this->setName('cloud:passwd')
            ->setDescription('modify users passwords')
            ->addArgument('username', InputArgument::OPTIONAL, 'username')
            ->addArgument('password', InputArgument::OPTIONAL, 'password')
            ->setHelp('');
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

        // read username
        if ($input->getArgument('username') !== null) {
            $username = $input->getArgument('username');
        } else {
            $question = new Question('Please enter the name of the User:');
            $question->setAutocompleterValues($this->getContainer()
                ->get('cloud.ldap.userprovider')
                ->getUsernames());
            $username = $helper->ask($input, $output, $question);
        }

        // check username for existence
        try {
            $this->user = $this->getContainer()
                ->get('cloud.ldap.userprovider')
                ->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            $output->writeln('User not found');

            return 1;
        }

        // read password
        if ($input->getArgument('password')) {
            $this->password = $input->getArgument('password');
        } else {
            $question = new Question('Please enter password:');
            $question->setHidden(true);
            $this->password = $helper->ask($input, $output, $question);
        }

        $password = $this->user->getPasswordObject();
        $password->setPasswordPlain($this->password);

        $this->getContainer()
            ->get('cloud.ldap.util.usermanipulator')
            ->update($this->user);

        return 0;
    }

    /**
     * request username, ether by argument or by user input
     *
     * @return string
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
}
