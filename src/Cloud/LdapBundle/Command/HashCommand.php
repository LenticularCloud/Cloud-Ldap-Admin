<?php
namespace Cloud\LdapBundle\Command;

use Cloud\LdapBundle\Security\CryptEncoder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Cloud\LdapBundle\Entity\User;
use Cloud\LdapBundle\Entity\Password;

class HashCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('ldap:hash')
            ->setDescription('hashes a password and return the result')
            ->setHelp("")
            ->addArgument('password',InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // read password
        $password="";
        if ($input->getArgument('password')) {
            $password = $input->getArgument('password');
        } else {
            $question = new Question('Please enter password:');
            $question->setHidden(true);
            $password = $helper->ask($input, $output, $question);
        }

        $encoder=new CryptEncoder();

        $pw= new Password();
        $pw->setPasswordPlain($password);

        $encoder->encodePassword($pw);

        $output->writeln($pw->getHash());
        
        return 0;
    }

}
