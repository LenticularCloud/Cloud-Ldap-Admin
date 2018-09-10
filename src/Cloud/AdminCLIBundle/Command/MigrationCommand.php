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

class MigrationCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('cloud:migration')
            ->setDescription('updates database from the old system')
            ->addOption('force', '-f', InputOption::VALUE_NONE, 'force update without asking')
            ->setHelp('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        try {
            $userprovider = $this->getContainer()->get('cloud.ldap.userprovider');
            $usermanipulator = $this->getContainer()->get('cloud.ldap.util.usermanipulator');
        } catch (\Exception $e) {
            $output->writeln("<error>Can't connect to database</error>");
            return 255;
        }


        if (!$input->getOption('force')) {
            $question = new ConfirmationQuestion('You realy whant to update the database? (do you have a backup?) [y/N]:', false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<error>canceled by user, if you use a script use \'-f\' to force</error>');

                return 1;
            }
        }


        foreach ($userprovider->getUsers() as $user) {
            if( $user instanceof User) {
                $mailService = $user->getService('mail');
                if(!$mailService->isEnabled()){
                    $mailService->setEnabled(true);
                    foreach($mailService->getPasswords() as $password ){
                        $mailService->removePassword($password);
                    }
                    $usermanipulator->update($user);
                }
            }

        }


        return 0;
    }
}