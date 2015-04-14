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

class AddUserCommand extends ContainerAwareCommand
{
	
  
  protected function configure()
  {
    $this
        ->setName('user:add')
        ->setDescription('Add an new user')
        ->addArgument('username',InputArgument::OPTIONAL,null)
        ->addArgument('password',InputArgument::OPTIONAL,null)
        ->setHelp("")
        //optional parameter
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $user=new User();

    $helper = $this->getHelper('question');

    //read username
    if($input->getArgument('username')) {
      $username=$input->getArgument('username');
      if(preg_match("/^[a-zA-Z0-9_.-]{2,}$/",$username)==0) {
        $output->writeln('invalide username');
        return 1;
      }
    } else {
      $question = new Question('Please enter the name of the new User:');
      $username='';
      while(preg_match("/^[a-zA-Z0-9_.-]{2,}$/",$username)==0) {
        $username= $helper->ask($input, $output, $question);
      }
    }
    $user->setUsername($username);

    //read password
    if($input->getArgument('password')) {
      $password=$input->getArgument('password');
    } else {
      $question = new Question('Please enter password:');
      $question->setHidden(true);
      $password=$helper->ask($input, $output, $question);
    }
    $user->setPassword(new Password($password,null));

    //comfirm
    $question = new ConfirmationQuestion('Do you want to create the user "'.$user->getUsername().'"? [y/N] ', false);
    if (!$helper->ask($input, $output, $question)) {
      $output->writeln('Canceled.');
      return 1;
    }
    
    $this->getContainer()->get('cloud.ldap')->createUser($user);
    $output->writeln('User added.');
    return 0;
	}
}
