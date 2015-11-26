<?php
namespace Cloud\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cloud\LdapBundle\Entity\Password;
use Cloud\FrontBundle\Form\Type\PasswordType;
use Symfony\Component\BrowserKit\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Cloud\FrontBundle\Form\Type\ServiceType;
use Cloud\LdapBundle\Entity\Service;
use Cloud\FrontBundle\Form\Type\NewPasswordType;

/**
 * @Route("/profile")
 */
class ProfileController extends Controller
{

    /**
     * @Route("/",name="profile")
     * @Template()
     */
    public function indexAction()
    {
        dump($this->get('security.token_storage')
            ->getToken()
            ->getUser());
        
        $formEdit = array();
        foreach ($this->getUser()->getServices() as $service) {
            $formEdit[$service->getName()] = array();
            
            foreach ($service->getPasswords() as $password) {
                if (! $password->isMasterPassword()) {
                    $form = $this->createForm(new PasswordType(), $password, array(
                        'action' => $this->generateUrl('profile_service_password_edit', array(
                            'serviceName' => $service->getName(),
                            'passwordId' => $password->getId()
                        )),
                        'method' => 'POST'
                    ));
                    $form->get('id_old')->setData($password->getId());
                    $formEdit[$service->getName()][] = $form->createView();
                }
            }
            $newPassword = new Password();
            $newPassword->setService($service);
            $formEdit[$service->getName()][] = $this->createForm(new NewPasswordType(), $newPassword, array(
                'action' => $this->generateUrl('profile_service_password_new', array(
                    'serviceName' => $service->getName()
                )),
                'method' => 'POST'
            ))
                ->createView();
        }
        
        $formEditMaster = array();
        foreach ($this->getUser()->getPasswords() as $password) {
            $form = $this->createForm(new PasswordType(), $password, array(
                'action' => $this->generateUrl('profile_password_edit', array(
                    'passwordId' => $password->getId()
                )),
                'method' => 'POST'
            ));
            $form->get('id_old')->setData($password->getId());
            $formEditMaster[] = $form->createView();
        }
        $newPassword = new Password();
        $formEditMaster[] = $this->createForm(new NewPasswordType(), $newPassword, array(
            'action' => $this->generateUrl('profile_password_new'),
            'method' => 'POST'
        ))
            ->createView();
        
        $formEditServiceMasterPassword=array();
        foreach ($this->getUser()->getServices() as $service) {
            $form = $this->createForm(new ServiceType(), $service, array(
                'action' => $this->generateUrl('profile_service_masterPassword_edit', array(
                    'service' => $service->getName()
                )),
                'method' => 'POST'
            ));
            $formEditServiceMasterPassword[$service->getName()] = $form->createView();
        }
        
        return array(
            'formEdit' => $formEdit,
            'formEditMasterPasswords' => $formEditMaster,
            'formEditServiceMasterPassword'=>$formEditServiceMasterPassword
        );
    }

    /**
     * @Route("/service/{serviceName}/password/{passwordId}/edit",name="profile_service_password_edit")
     * @Route("/password/{passwordId}/edit",name="profile_password_edit")
     * @Method("POST")
     * @Template()
     */
    public function passwordEditAction($passwordId, $serviceName = null)
    {
        $user = $this->getUser();

        dump($serviceName);
        if ($serviceName === null) {
            $password = $user->getPassword($passwordId);
        } else {
            $password = $user->getService($serviceName)->getPassword($passwordId);
        }
        
        $form = $this->createForm(new PasswordType(),$password);
        $form->bind($this->getRequest());
        
        if ($form->isValid()) {
            
            dump($form->get('remove')->isClicked());
            
            if($form->get('remove')->isClicked()) {
                if ($serviceName == null) {
                    $user->removePassword($password);
                } else {
                    $user->getService($serviceName)->removePassword($password);
                }
            }
            $this->get('cloud.ldap.util.usermanipulator')->update($user);
        }
        
        return $this->redirect($this->generateUrl('profile'));
        ;
    }

    /**
     * @Route("/service/{serviceName}/password/new",name="profile_service_password_new")
     * @Route("/password/new",name="profile_password_new")
     * @Method("POST")
     * @Template()
     */
    public function passwordNewAction($service = null)
    {
        $user = $this->getUser();
        $form = $this->createForm(new NewPasswordType());
        $form->bind($this->getRequest());
        
        if ($form->isValid()) {
            $password = $form->getData();
            dump($service);
            if ($service === null) {
                $password->setMasterPassword(true);
                $user->addPassword($password);
            } else {
                $user->getService($service)->addPassword($password);
            }
            
            $this->get('cloud.ldap.util.usermanipulator')->update($user);
        }
        
        
        return $this->redirect($this->generateUrl('profile'));
    }

    /**
     * @Route("/service/{serviceName}/password/{passwordID}/delete",name="profile_password_delete")
     * @Route("/password/{passwordID}/delete",name="profile_password_delete")
     * @Method("POST")
     * @Template()
     */
    public function passwordDeleteAction($passwordID, $serviceName = null)
    {
        $user = $this->getUser();
        
        if ($serviceName == null) {
            $password = $user->getPassword($passwordID);
            $user->removePassword($password);
        } else {
            $password = $user->getService($serviceName)->getPassword($passwordID);
            $user->getService($serviceName)->removePassword($password);
        }
        
        $this->get('cloud.ldap.util.usermanipulator')->update($user);
        
        return $this->redirect($this->generateUrl('profile'));
    }

    /**
     * @Route("/service/{service}/password/masterPassword",name="profile_service_masterPassword_edit")
     * @Method("POST")
     * @Template()
     */
    public function serviceMasterPasswordEditAction($service)
    {
        $user = $this->getUser();
        $service=$user->getService($service);
        
       $form = $this->createForm(new ServiceType(), $service);
        $form->bind($this->getRequest());
        if ($form->isValid()) {
            $this->get('cloud.ldap.util.usermanipulator')->update($user);
        }
        dump($form);
        

        
        return $this->redirect($this->generateUrl('profile'));
        ;
    }
}
