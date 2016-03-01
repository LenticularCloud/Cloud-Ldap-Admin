<?php
namespace Cloud\AdminCLIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Cloud\LdapBundle\Entity\User;
use Cloud\LdapBundle\Entity\Password;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class UpdateCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('cloud:update')
            ->setDescription('updates database, to add new services')
            //->addOption('force', '-f', InputOption::VALUE_NONE, 'force deletes an user without asking')
            ->setHelp('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        try {
            $this->getContainer()->get('cloud.ldap.schema.manipulator');
        } catch (\Exception $e) {
            $output->writeln("<error>Can't connect to database</error>");
            return 255;
        }

        $this->getContainer()
            ->get('cloud.ldap.schema.manipulator')->updateSchema();

        return 0;
    }
}