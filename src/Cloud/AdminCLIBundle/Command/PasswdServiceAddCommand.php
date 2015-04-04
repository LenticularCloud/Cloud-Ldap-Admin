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
        //parameter: [username [service [password id]]]
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
    
    //print/read service into $servicename
    while(!isset($service)) {
      foreach($user->getServices() as $service) {
        $output->writeln("service");
        //print
      }
      //read name into $servicename
      
      $service=$user->getService($servicename);
    }
    
    //read new password/comment
    
    $service->addPassword(new Password($password,$comment));
    
	}
}
