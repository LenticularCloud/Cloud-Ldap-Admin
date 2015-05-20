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

class ServiceCommand extends ContainerAwareCommand
{

    /**
     *
     * @var \Cloud\LdapBundle\Entity\User
     */
    private $user;

    /**
     *
     * @var string
     */
    private $serviceName;

    protected function configure()
    {
        $this->setName('cloud:service')
            ->setDescription('activate or disable a service for an user')
            ->addArgument('username', InputArgument::OPTIONAL, null)
            ->addArgument('service', InputArgument::OPTIONAL, null)
            ->addOption('force', '-f', InputOption::VALUE_NONE, 'force deletes an service without asking')
            ->addOption('add', '-a', InputOption::VALUE_NONE, 'enables an new service to a user')
            ->addOption('delete', '-d', InputOption::VALUE_NONE, 'force deletes an service with all passwords')
            ->addOption('json', '-j', InputOption::VALUE_NONE, 'json output')
            ->setHelp('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        
        try {
            $this->getContainer()->get('cloud.ldap');
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
        $username = null;
        if ($input->getArgument('username') !== null) {
            $username = $input->getArgument('username');
        } else {
            $question = new Question('Please enter the name of the User:');
            if ($input->getOption('delete')) {
                $question->setAutocompleterValues($this->getContainer()
                    ->get('cloud.ldap')
                    ->getAllUsernames());
            }
            $username = $helper->ask($input, $output, $question);
        }
        
        // check username for existence
        try {
            $this->user = $this->getContainer()
                ->get('cloud.ldap')
                ->getUserByUsername($username);
        } catch (UserNotFoundException $e) {
            $output->writeln('User not found');
            return 1;
        }
        
        // if no option is set show service list
        if (! $input->getOption('add') && ! $input->getOption('delete')) {
            $services = array_map(function ($value) {
                return $value->getName();
            }, $this->user->getServices());
            
            if ($input->getOption('json')) {
                $output->writeln(json_encode($services));
            } else {
                foreach ($services as $username) {
                    $output->writeln($username);
                }
            }
            return 0;
        }
        
        // read service
        if ($input->getArgument('service')) {
            $this->serviceName = $input->getArgument('service');
        } else {
            $question = new Question('Please enter service name:');
            $question->setAutocompleterValues($this->getContainer()
                ->get('cloud.ldap')
                ->getServices());
            $this->serviceName = $helper->ask($input, $output, $question);
        }
        
        if ($input->getOption('delete')) {
            if (! $input->getOption('force')) {
                $question = new ConfirmationQuestion('You realy whant to disable this service for the user \'' . $this->user->getUsername() . "?\n<error>all passwords get deleted</error> [y/N]:", false);
                if (! $helper->ask($input, $output, $question)) {
                    $output->writeln('<error>canceled by user, if you use a script use \'-f\' to force delete</error>');
                    return 1;
                }
            }
            
            $this->getContainer()
                ->get('cloud.ldap')
                ->disableService($this->user, $this->serviceName);
            return 0;
        }
        
        if ($input->getOption('add')) {
            
            $this->getContainer()
                ->get('cloud.ldap')
                ->enableService($this->user, $this->serviceName);
            return 0;
        }
    }
}