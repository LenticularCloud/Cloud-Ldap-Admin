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
        
        // if no option is set show user list
        if (! $input->getOption('add') && ! $input->getOption('delete')) {
            if ($input->getOption('json')) {
                $output->writeln(json_encode($this->getContainer()
                    ->get('cloud.ldap')
                    ->getAllUsernames()));
            } else {
                foreach ($this->getContainer()
                    ->get('cloud.ldap')
                    ->getAllUsernames() as $username) {
                    $output->writeln($username);
                }
            }
            return 0;
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
        
        if ($input->getOption('delete')) {
            if (! $input->getOption('force')) {
                $question = new ConfirmationQuestion('You realy whant to delete this user? [y/N]:', false);
                if (! $helper->ask($input, $output, $question)) {
                    $output->writeln('<error>canceled by user, if you use a script use \'-f\' to force delete</error>');
                    return 1;
                }
            }
            
            $user = $this->getContainer()
                ->get('cloud.ldap')
                ->getUserByUsername($username);
            
            if ($user == null) {
                $output->writeln("<error>can't find user</error>");
            }
            
            $this->getContainer()
                ->get('cloud.ldap')
                ->deleteUser($user);
            return 0;
        }
        
        if ($input->getOption('add')) {
            $user = $this->getContainer()
                ->get('cloud.ldap')
                ->getUserByUsername($username);
            
            if ($user != null) {
                $output->writeln("<error>username allready taken</error>");
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
            $user = new User($username);
            $user->addPassword(new Password('master', $password));
            
            $this->getContainer()
                ->get('cloud.ldap')
                ->createUser($user);
            return 0;
        }
        
        return 0;
    }
}
