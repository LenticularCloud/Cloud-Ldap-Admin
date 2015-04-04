<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Cloud\CLIBundle\Exception\UserNotFound;

class PasswdCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('user:passwd:master')
        ->setDescription('change the master password from a user')
        //parameter: [username [password]]
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    
    //read username into $username
    
    try {
      $user=$this->get('cloud.ldap')->getUserByUsername($username);
    }catch (UserNotFound $e) {
      //print error
      return 1;
    }
    
    //read password
    
    $user->setPassword($password);
    
    $this->get('cloud.ldap')->updateUser($user);
	}
}
