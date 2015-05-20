<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

use Cloud\LdapBundle\Entity\User;
use Cloud\LdapBundle\Entity\Password;

class InitCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('cloud:init')
        ->setDescription('init the ldap database')
        ->setHelp("")
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
  	$this->getContainer()->get('cloud.ldap')->init();
  	
    return 0;
	}
}
