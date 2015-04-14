<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\Question;

class ListUsersCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('user:list')
        ->setDescription('lists all the current users')
        ->addOption('json','-j',InputOption::VALUE_NONE,'Output as JSON')
        ->setHelp('outputs a list of users as list or as json')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    
    if($input->getOption('json')) {
      $output->writeln(json_encode($this->getContainer()->get('cloud.ldap')->getAllUsernames()));
    }else {
      foreach($this->getContainer()->get('cloud.ldap')->getAllUsernames() as $username) {
        $output->writeln($user->getUsername());
      }
    }

    return 0;
	}
}
