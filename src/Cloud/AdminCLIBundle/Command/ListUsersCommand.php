<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListUsersCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('user:list')
        ->setDescription('lists all the current users')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    
    $users=$this->getContainer()->get('cloud.ldap')->getAllUsers();
    
    foreach($users as $user) {
      //list users
    }
	}
}
