<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Cloud\LdapBundle\Entity\Password;
use Symfony\Component\Console\Command\Command;
use Cloud\LdapBundle\Entity\User;
use Symfony\Component\Console\Question\ChoiceQuestion;

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
            ->addArgument('id', InputArgument::OPTIONAL, 'password id')
            ->addArgument('password', InputArgument::OPTIONAL, 'password')
            ->addOption('service', 'S', InputOption::VALUE_OPTIONAL, 'service name, use \'.\' for input field')
            ->addOption('add', 'a', InputOption::VALUE_NONE, 'add, default if no password with this id exist')
            ->addOption('modify', 'm', InputOption::VALUE_NONE, 'modify, default if password id exist')
            ->addOption('delete', 'd', InputOption::VALUE_NONE, 'delete password')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'force creation of password, also if service not exist for this user')
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
        
        // check options
        $action = null;
        try {
            $action = $this->getAction($input->getOptions());
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' + $e->getMessage() + '</error>');
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
        } catch (UserNotFoundException $e) {
            $output->writeln('User not found');
            return 1;
        }
        
        // check for service attribute
        $service = null;
        if ($input->getOptions()['service'] !== null) {
            $serviceName = $input->getOption('service');
            if ($serviceName == '.') {
                $question = new Question('Please enter the name of the Service:');
                $question->setAutocompleterValues($this->getContainer()
                    ->get('cloud.ldap.userprovider')
                    ->getServiceNames());
                $serviceName = $helper->ask($input, $output, $question);
            }
            $service = $this->user->getService($serviceName);
            if ($service == null) {
                $output->writeln('<error>can\'t find service</error>');
                return 1;
            }
        }
        
        if ($action == null) {
            $passwords = null;
            if ($service != null) {
                $passwords = $service->getPasswords();
            } else {
                $passwords = $this->user->getPasswords();
            }
            foreach ($passwords as $password) {
                $output->writeln($password->getId());
            }
            
            return 0; // complete listed alls passwords
        }
        
        // read id
        if ($input->getArgument('id')) {
            $this->passwordId = $input->getArgument('id');
        } else {
            $question = new Question('Please enter password id:');
            if ($service != null) {
                $passwords = $service->getPasswords();
            } else {
                $passwords = $this->user->getPasswords();
            }
            
            $question->setAutocompleterValues(array_keys($passwords));
            
            $this->passwordId = $helper->ask($input, $output, $question);
        }
        
        if ($action == "delete") {
            if (! $input->getOption('force')) {
                $question = new ConfirmationQuestion('You realy whant to delete this password? [y/N]:', false);
                if (! $helper->ask($input, $output, $question)) {
                    $output->writeln('<error>canceled by user, if you use a script use \'-f\' to force delete</error>');
                    return 1;
                }
            }
            
            if ($service != null) {
                $password = $service->getPassword($this->passwordId);
                
                if ($password == null) {
                    $output->writeln('<error>can\'t find password with this id</error>');
                    return 1;
                }
                
                $service->removePassword($password);
            } else {
                $password = $this->user->getPassword($this->passwordId);
                
                if ($password == null) {
                    $output->writeln('<error>can\'t find password with this id</error>');
                    return 1;
                }
                
                $this->user->removePassword($password);
            }
            $this->getContainer()
                ->get('cloud.ldap.util.usermanipulator')
                ->update($this->user);
            return 0; // complete delete a password
        }
        
        // read password
        if ($input->getArgument('password')) {
            $this->password = $input->getArgument('password');
        } else {
            $question = new Question('Please enter password:');
            $question->setHidden(true);
            $this->password = $helper->ask($input, $output, $question);
        }
        
        if ($action == "add") {
            $password = new Password($this->passwordId, $this->password);
            if ($service != null) {
                $service->addPassword($password);
            } else {
                $this->user->addPassword($password);
            }
            
            $this->getContainer()
                ->get('cloud.ldap.util.usermanipulator')
                ->update($this->user);
            return 0;
        }
        if ($action == "modify") {
            if ($service != null) {
                $password = $service->getPassword($this->passwordId);
            } else {
                $password = $this->user->getPassword($this->passwordId);
            }
            $password->setPasswordPlain($this->password);
            
            $this->getContainer()
                ->get('cloud.ldap')
                ->updateUser($this->user);
            return 0;
        }
    }

    /**
     *
     * @param unknown $options            
     * @throws \InvalidArgumentException
     */
    private function getAction(array $options)
    {
        $tmp = 0;
        $tmp += $options["add"] ? 1 : 0;
        $tmp += $options["delete"] ? 1 : 0;
        $tmp += $options["modify"] ? 1 : 0;
        if ($tmp > 1) {
            throw new \InvalidArgumentException("can't make multiple changes (add|delete|modify) with a password");
        }
        if ($options["add"])
            return "add";
        if ($options["delete"])
            return "delete";
        if ($options["modify"])
            return "modify";
        return null; // no command
    }
}
