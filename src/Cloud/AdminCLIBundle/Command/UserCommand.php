<?php
namespace Cloud\AdminCLIBundle\Command;

use Cloud\LdapBundle\Entity\Doctrine\Setting;
use Cloud\LdapBundle\Security\CryptEncoder;
use Cloud\LdapBundle\Security\NtEncoder;
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
        
        // if no option is set show user list
        if (! $input->getOption('add') && ! $input->getOption('delete')) {
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
        
        // read username
        $username = null;
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
        
        if ($input->getOption('delete')) {
            if (! $input->getOption('force')) {
                $question = new ConfirmationQuestion('You realy whant to delete this user? [y/N]:', false);
                if (! $helper->ask($input, $output, $question)) {
                    $output->writeln('<error>canceled by user, if you use a script use \'-f\' to force delete</error>');
                    return 1;
                }
            }
            try {
                $user = $this->getContainer()
                    ->get('cloud.ldap.userprovider')
                    ->loadUserByUsername($username);
            }catch (UsernameNotFoundException $e) {
                $output->writeln("<error>can't find user</error>");
            }
            
            $this->getContainer()
                ->get('cloud.ldap.util.usermanipulator')
                ->delete($user);
            return 0;
        }
        
        if ($input->getOption('add')) {
            $em = $this->getContainer()->get('doctrine')->getManager();

            try {
                $user = $this->getContainer()
                    ->get('cloud.ldap.userprovider')
                    ->loadUserByUsername($username);
                $output->writeln("<error>username allready taken</error>");
                return 1;
            }catch(UsernameNotFoundException $e) {
            }
            
            // read password
            $passwordPlain = null;
            if ($input->getArgument('password')) {
                $passwordPlain = $input->getArgument('password');
            } else {
                $question = new Question('Please enter password:');
                $question->setHidden(true);
                $passwordPlain = $helper->ask($input, $output, $question);
            }
            $userLdap=$this->getContainer()->get('cloud.ldap.util.usermanipulator')->createUser($username);
            $uid=$em->getRepository(\Cloud\LdapBundle\Entity\Doctrine\Setting::class)->findOneByKey('posixAccount.nextUid');
            if($uid===null) {
                $uid=new Setting('posixAccount.nextUid');
                $uid->setValue('20000');
                $em->persist($uid);
            }

            $userLdap = $this->getContainer()->get('cloud.ldap.util.usermanipulator')->createUser($userLdap->getUsername());
            $userLdap->setUidNumber($uid->getValue());
            $userLdap->setGidNumber($uid->getValue());
            $userLdap->setSambaSID('S-1-5-21-2919324557-891694127-41725'.$uid->getValue());
            $uid->setValue($uid->getValue()+1);
            $em->flush();

            $password = new Password();
            $password->setId('default');
            $password->setPasswordPlain($passwordPlain);
            CryptEncoder::encodePassword($password);
            $userLdap->setPassword($password);

            $password = new Password();
            $password->setId('default');
            $password->setPasswordPlain($passwordPlain);
            NtEncoder::encodePassword($password);
            $userLdap->setNtPassword($password);

            $this->getContainer()
                ->get('cloud.ldap.util.usermanipulator')
                ->create($userLdap);
            return 0;
        }
        
        return 0;
    }
    
    private function add()
    {
        
    }
}
