<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

use Cloud\LdapBundle\Exception\UserNotFoundException;
use Cloud\LdapBundle\Entity\Password;

class PasswdCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('user:passwd')
        ->setDescription('change the master password from a user')
        ->addArgument('username',InputArgument::OPTIONAL,null)
        ->addArgument('password',InputArgument::OPTIONAL,null)
        ->setHelp('')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $helper = $this->getHelper('question');
    
    //read username
    if($input->getArgument('username')) {
      $username=$input->getArgument('username');
    } else {
      $question = new Question('Please enter the name of the User:');
      $question->setAutocompleterValues($this->getContainer()->get('cloud.ldap')->getAllUsernames());
      $username= $helper->ask($input, $output, $question);
    }
    
    try {
      $user=$this->getContainer()->get('cloud.ldap')->getUserByUsername($username);
    }catch (UserNotFound $e) {
      $output->writeln('User not found');
      return 1;
    }

    //read password
    if($input->getArgument('password')) {
      $password=$input->getArgument('password');
    } else {
      $question = new Question('Please enter password:');
      $question->setHidden(true);
      $password=$helper->ask($input, $output, $question);
    }
    $user->setPassword(new Password($password,null));
    
    $this->get('cloud.ldap')->updateUser($user);
	}
}
