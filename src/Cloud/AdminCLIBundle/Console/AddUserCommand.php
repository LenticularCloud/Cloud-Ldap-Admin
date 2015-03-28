<?php
namespace Cloud\CLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddUserCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('user:add')
        ->setDescription('Add an new user')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    //...
	}
}
