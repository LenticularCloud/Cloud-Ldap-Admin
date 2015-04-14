<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Cloud\LdapBundle\Exception\UserNotFound;

class PasswdServiceAddCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('user:passwd:service:add')
        ->setDescription('add a userpassword to a specific service')
        ->addArgument('username',InputArgument::OPTIONAL,null)
        ->addArgument('service',InputArgument::OPTIONAL,null)
        ->addArgument('password',InputArgument::OPTIONAL,null)
        ->addArgument('id',InputArgument::OPTIONAL,null)
        ->setHelp("parameter: [username [service [password id]]]")
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
      $password=$input->getArgument('password');
    } else {
      $question = new Question('Please enter password:');
      $question->setHidden(true);
      $password=$helper->ask($input, $output, $question);
    }

    //read password
    if($input->getArgument('password')) {
      $password=$input->getArgument('password');
    } else {
      $question = new Question('Please enter password:');
      $question->setHidden(true);
      $password=$helper->ask($input, $output, $question);
    }
    
    //read id
    if($input->getArgument('id')) {
      $passwordId=$input->getArgument('id');
    } else {
      $question = new Question('Please enter password id:');
      $passwordId=$helper->ask($input, $output, $question);
    }
    
    if(preg_match("/^[a-zA-Z0-9_.-]{2,}$/",$passwordId)!=0) {
      $output->writeln('Invalide id.');
      return 1;
    }
    
    if($user->getService($service)==null) {
      $output->writeln('service not found')
    }
    
    $user->getService($service)->setPassword(new Password($password,null));
    
    $service->addPassword(new Password($password,$comment));
    
	}
}
