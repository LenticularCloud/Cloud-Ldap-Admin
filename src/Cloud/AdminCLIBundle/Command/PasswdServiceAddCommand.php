<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use Cloud\LdapBundle\Exception\UserNotFoundException;
use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Entity\Service;

class PasswdServiceAddCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('user:passwd:service:add')
        ->setDescription('add a userpassword to a specific service')
        ->addArgument('username',InputArgument::OPTIONAL,null)
        ->addArgument('service',InputArgument::OPTIONAL,null)
        ->addArgument('id',InputArgument::OPTIONAL,null)
        ->addArgument('password',InputArgument::OPTIONAL,null)
        ->addOption('force','f',InputOption::VALUE_NONE,'force creation of password, also if service not exist for this user')
        ->setHelp("parameter: [-f] [username] [service] [id] [password]")
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $helper = $this->getHelper('question');
    
    //read username
    if($input->getArgument('username')) {
      $username=$input->getArgument('username');
    } else {
      $question = new Question('Please enter the name of the User:');
      $question->setAutocompleterValues($this->getContainer()->get('cloud.ldap')->getAllUsernames());
      $username= $helper->ask($input, $output, $question);
    }
    
    try {
      $user=$this->getContainer()->get('cloud.ldap')->getUserByUsername($username);
    }catch (UserNotFound $e) {
      $output->writeln('User not found');
      return 1;
    }

    //read service
    if($input->getArgument('service')) {
      $service=$input->getArgument('service');
    } else {
      $question = new Question('Please enter service:');
  		$question->setAutocompleterValues($this->getContainer()->get('cloud.ldap')->getServices());
      $service=$helper->ask($input, $output, $question);
    }
    
    //read id
    if($input->getArgument('id')) {
      $passwordId=$input->getArgument('id');
    } else {
      $question = new Question('Please enter password id:');
      $passwordId=$helper->ask($input, $output, $question);
    }
    //@TODO global validation
    if(preg_match("/^[a-zA-Z0-9_.-]{2,}$/",$passwordId)==0) {
      $output->writeln('Invalide id.');
      return 1;
    }

    //read password
    if($input->getArgument('password')) {
      $password=$input->getArgument('password');
    } else {
      $question = new Question('Please enter password:');
      $question->setHidden(true);
      $password=$helper->ask($input, $output, $question);
    }
    
    
    if($user->getService($service)==null) {
    	if(in_array($service, $this->getContainer()->get('cloud.ldap')->getServices())) {
    		if($input->getOption('force')) {
	    		$tmp=new Service();
	    		$tmp->setName($service);
	    		$user->addService($tmp);
    		}else {
      		$output->writeln('service not found for this user, use -f to create it');
      		return 1;
    		}
    	} else {
      	$output->writeln('service not found');
      	return 1;
    	}
    }
    
    $user->getService($service)->addPassword(new Password($password,$passwordId));
    $this->getContainer()->get('cloud.ldap')->updateUser($user);
    
	}
}
