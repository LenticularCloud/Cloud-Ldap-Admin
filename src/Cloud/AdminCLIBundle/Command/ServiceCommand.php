<?php
namespace Cloud\AdminCLIBundle\Command;

use Cloud\LdapBundle\Entity\Service;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Cloud\LdapBundle\Entity\User;
use Cloud\LdapBundle\Entity\Password;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class ServiceCommand extends ContainerAwareCommand
{

    /**
     *
     * @var \Cloud\LdapBundle\Entity\User
     */
    private $user;

    /**
     *
     * @var Service
     */
    private $service;

    protected function configure()
    {
        $this->setName('cloud:service')
            ->setDescription('activate or disable a service for an user')
            ->addArgument('username', InputArgument::OPTIONAL, null)
            ->addArgument('action', InputArgument::OPTIONAL, "[list|show|enable|disable|enableMasterPassword|disableMasterPassword]")
            ->addArgument('service', InputArgument::OPTIONAL, null)
            ->addOption('force', '-f', InputOption::VALUE_NONE, 'force deletes an service without asking')
            /*enable 'enables an new service to a user'
            ->addOption('disable', '-d', InputOption::VALUE_NONE, 'force deletes an service with all passwords')
            ->addOption('disableMasterPws', null, InputOption::VALUE_NONE, 'disables the master passwords for this service')
            ->addOption('enableMasterPws', null, InputOption::VALUE_NONE, 'enable the master passwords for this service')*/
            ->addOption('json', '-j', InputOption::VALUE_NONE, 'json output')
            ->setHelp('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        try {
            $this->getContainer()->get('cloud.ldap.userprovider');
        } catch (\Exception $e) {
            $output->writeln("<error>Can't connect to database</error>");
            return 255;
        }

        // read username
        $username = null;
        if ($input->getArgument('username') !== null) {
            $username = $input->getArgument('username');
        } else {
            $question = new Question('Please enter the name of the User:');
            $question->setAutocompleterValues($this->getContainer()
                ->get('cloud.ldap.userprovider')
                ->getUsernames());
            $username = $helper->ask($input, $output, $question);
        }

        // check username for existence
        try {
            $this->user = $this->getContainer()
                ->get('cloud.ldap.userprovider')
                ->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            $output->writeln('<error>User not found</error>');
            return 1;
        }


        $action = null;
        if ($input->getArgument('action') !== null) {
            $action = $input->getArgument('action');
        } else {
            $question = new Question('Please enter a action (list|show|enable|disable|enableMasterPassword|disableMasterPassword):');
            $question->setAutocompleterValues(['list', 'show', 'enable', 'disable', 'enableMasterPassword', 'disableMasterPassword']);
            $action = $helper->ask($input, $output, $question);
        }

        if ($action === 'list') {
            $table = new Table($output);
            $table
                ->setHeaders(array('Service Name', 'Enabled', 'MasterPasswordEnabled', 'passwords'));
            foreach ($this->user->getServices() as $service) {
                $table->addRow([$service->getName(), $service->isEnabled() ? 'X' : '', $service->isMasterPasswordEnabled() ? 'X' : '', implode(',',
                    array_map(function ($password) {
                        return !$password->isMasterPassword() ? $password->getId() : '';
                    }, $service->getPasswords()))
                ]);
            }
            $table->render();
            return 0;
        }


        // read service
        $serviceName = null;
        if ($input->getArgument('service')) {
            $serviceName = $input->getArgument('service');
        } else {
            $question = new Question('Please enter service name:');
            $question->setAutocompleterValues(array_map(function ($service) {
                return $service->getName();
            }, $this->user->getServices()));
            $serviceName = $helper->ask($input, $output, $question);
        }

        $this->service = $this->user->getService($serviceName);


        switch ($action) {
            case 'show':
                //@TODO
                throw new NotImplementedException("show not implemented");
                break;
            case 'enable':
                $this->service->setEnabled(true);
                $this->service->setMasterPasswordEnabled(true);

                $this->getContainer()
                    ->get('cloud.ldap.util.usermanipulator')
                    ->update($this->user);
                break;
            case 'disable':
                if (!$input->getOption('force')) {
                    $question = new ConfirmationQuestion('You really want to disable this service for the user \'' . $this->user->getUsername() . "?\n<error>all passwords get deleted</error> [y/N]:", false);
                    if (!$helper->ask($input, $output, $question)) {
                        $output->writeln('<error>canceled by user, if you use a script use \'-f\' to force delete</error>');
                        return 1;
                    }
                }

                $this->service->setEnabled(false);
                $this->getContainer()
                    ->get('cloud.ldap.util.usermanipulator')
                    ->update($this->user);
                break;
            case 'enableMasterPassword':
                $this->service->setMasterPasswordEnabled(true);
                $this->getContainer()
                    ->get('cloud.ldap.util.usermanipulator')
                    ->update($this->user);
                break;
            case 'disableMasterPassword':
                $this->service->setMasterPasswordEnabled(false);
                $this->getContainer()
                    ->get('cloud.ldap.util.usermanipulator')
                    ->update($this->user);
                break;
            default:
                $output->writeln('<error>action not found</error>');
                return 1;
        }
        return 0;
    }
}