<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Cloud\LdapBundle\Entity\User;
use Cloud\LdapBundle\Entity\Password;

class AddUserCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('user:add')
        ->setDescription('Add an new user')
        //optional parameter
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $user=new User();
    
    //read input and save into $user
    
    
    $this->container->get('cloud.ldap')->createUser($user);
	}
}
