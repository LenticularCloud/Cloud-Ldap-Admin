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
	}
}
