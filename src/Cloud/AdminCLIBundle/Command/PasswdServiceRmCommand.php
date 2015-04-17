<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Cloud\LdapBundle\Exception\UserNotFoundException;

class PasswdServiceRmCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('user:passwd:service:rm')
        ->setDescription('removes a password from a specific service')
        //parameter: [username [service [id]]]
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    throw new \BadFunctionCallException();
    //read username into $username
    /*
    try {
      $user=$this->get('cloud.ldap')->getUserByUsername($username);
    }catch (UserNotFound $e) {
      //print error
      return 1;
    }
    
    //print/read service into $servicename
    while(!isset($service)) {
      foreach($user->getServices() as $service) {
        //print
      }
      //read name into $servicename
      
      $service=$user->getService($servicename);
    }
    
    
    //print passwords list and save id into $password_id
    while(!isset($password_id)) {
      foreach($service->getPasswords() as $password) {
        //print password
      }
    }
    
    //remove password
     
     */
	}
}
