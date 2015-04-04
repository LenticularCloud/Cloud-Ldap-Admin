<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Cloud\CLIBuLdapBundlendle\Exception\UserNotFound;

class PasswdServiceListCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('user:passwd:service:list')
        ->setDescription('lists all the passwords of the given user')
        //parameter: [username [service]]
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
    
    

    if(isset($servicename)) {
      $services=$user->getServices();
      foreach($services[$servicename]->getPasswords() as $password) {
        $output->writeln('passwords...');
        //print passwords...
      }
    }else {
      foreach($user->getServices() as $service) {
        $output->writeln( $service->getName());
        foreach($service->getPasswords() as $password) {
          //print passwords...
        }
      }
    }
	}
}
