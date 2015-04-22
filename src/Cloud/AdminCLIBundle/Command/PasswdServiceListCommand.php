<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use Cloud\LdapBundle\Exception\UserNotFoundException;

class PasswdServiceListCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('user:passwd:service:list')
        ->setDescription('lists all the passwords of the given user')
        ->addArgument('username',InputArgument::OPTIONAL,null)
        ->addArgument('service',InputArgument::OPTIONAL,null)
        ->setHelp("parameter: [username [service]]")
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {

  	
  	$helper = $this->getHelper('question');
  	
  	//read username
  	if($input->getArgument('username')) {
  		$username=$input->getArgument('username');
  		if(preg_match("/^[a-zA-Z0-9_.-]{2,}$/",$username)==0) {
  			$output->writeln('invalide username');
  			return 1;
  		}
  	} else {
  		$question = new Question('Please enter the name of the new User:');
  		$question->setAutocompleterValues($this->getContainer()->get('cloud.ldap')->getAllUsernames());
  		$username= $helper->ask($input, $output, $question);
  	}
  	
  	//@TODO validate $username
  	try {
  		$user=$this->getContainer()->get('cloud.ldap')->getUserByUsername($username);
  	}catch (UserNotFoundException $e) {
  		$output->writeln('<error>User not found.</error>');
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
  	
  	if($user->getService($service)!=null) {
  		foreach ($user->getService($service)->getPasswords() as $password) {
  			$output->writeln($password->getId());
  		}
  	}else {
  		$output->writeln('no extra passwords found.');
  	}
  	
  	return 0;
	}
}
