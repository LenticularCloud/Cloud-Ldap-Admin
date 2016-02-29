<?php
namespace Cloud\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cloud\LdapBundle\Entity\Password;
use Cloud\FrontBundle\Form\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Cloud\FrontBundle\Form\Type\ServiceType;
use Cloud\LdapBundle\Entity\Service;
use Cloud\FrontBundle\Form\Type\NewPasswordType;
use Symfony\Component\Form\FormError;
use InvalidArgumentException;

/**
 * @Route("/password")
 */
class PasswordController extends Controller
{

    /**
     * @Route("/",name="password")
     * @Template()
     */
    public function indexAction()
    {
        
        //--- services ---
        $formEdit = array();
        $formEditServiceMasterPassword = array();
        foreach ($this->getUser()->getServices() as $service) {
            //-- service settings --
            $form = $this->createForm(new ServiceType(), $service, array(
                'action' => $this->generateUrl('password_service_edit', array(
                    'service' => $service->getName()
                )),
                'method' => 'POST'
            ));
            $formEditServiceMasterPassword[$service->getName()] = $form->createView();

            //--- service passwords ---
            $formEdit[$service->getName()] = array();
            
            if(!$service->isEnabled())  {
                continue;
            }
            
            foreach ($service->getPasswords() as $password) {
                if (! $password->isMasterPassword()) {
                    $form = $this->createForm(new PasswordType(), $password, array(
                        'action' => $this->generateUrl('password_service_password_edit', array(
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
                'action' => $this->generateUrl('password_service_password_new', array(
                    'serviceName' => $service->getName()
                )),
                'method' => 'POST'
            ))
                ->createView();
        }
        
        //---- master ---
        $formEditMaster = array();
        foreach ($this->getUser()->getPasswords() as $password) {
            $form = $this->createForm(new PasswordType(), $password, array(
                'action' => $this->generateUrl('password_password_edit', array(
                    'passwordId' => $password->getId()
                )),
                'method' => 'POST'
            ));
            $form->get('id_old')->setData($password->getId());
            $formEditMaster[] = $form->createView();
        }
        $newPassword = new Password();
        $formEditMaster[] = $this->createForm(new NewPasswordType(), $newPassword, array(
            'action' => $this->generateUrl('password_password_new'),
            'method' => 'POST'
        ))
            ->createView();
        
        
        $errors = $this->getRequest()
            ->getSession()
            ->getFlashBag()
            ->get('errors', array());
        
        return array(
            'errors' => $errors,
            'formEdit' => $formEdit,
            'formEditMasterPasswords' => $formEditMaster,
            'formEditServiceMasterPasswords' => $formEditServiceMasterPassword
        );
    }

    /**
     * @Route("/service/{serviceName}/password/{passwordId}/edit",name="password_service_password_edit")
     * @Route("/password/{passwordId}/edit",name="password_password_edit")
     * @Method("POST")
     * @Template()
     */
    public function passwordEditAction($passwordId, $serviceName = null)
    {
        $user = $this->getUser();
        
        if ($serviceName === null) {
            $password = $user->getPassword($passwordId);
        } else {
            $password = $user->getService($serviceName)->getPassword($passwordId);
        }
        
        $form = $this->createForm(new PasswordType(), $password);
        $form->bind($this->getRequest());
        
        if ($form->get('remove')->isClicked()) {
            if ($serviceName == null) {
                $user->removePassword($password);
            } else {
                $user->getService($serviceName)->removePassword($password);
            }
        }
        
        $errors = $this->get('validator')->validate($user);
        
        if (count($errors) === 0) {
            $this->get('cloud.ldap.util.usermanipulator')->update($user);
        } else {
            $this->getRequest()
                ->getSession()
                ->getFlashBag()
                ->set('errors', $this->render('CloudFrontBundle::error.html.twig', array(
                'errors' => $errors
            )));
        }
        
        return $this->redirect($this->generateUrl('password'));
    }

    /**
     * @Route("/service/{serviceName}/password/new",name="password_service_password_new")
     * @Route("/password/new",name="password_password_new")
     * @Method("POST")
     * @Template()
     */
    public function passwordNewAction($serviceName = null)
    {
        $user = $this->getUser();
        $form = $this->createForm(new NewPasswordType());
        
        $form->bind($this->getRequest());
        $password = $form->getData();
        try {
            if ($serviceName === null) {
                $user->getPassword($password->getId());
            }else {
                $user->getService($serviceName)->getPassword($password->getId());
            }
            $form->addError(new FormError("Password Id is in use"));
        }catch(InvalidArgumentException $e) {}
        
        if ($form->isValid()) {
            
            if ($serviceName === null) {
                $password->setMasterPassword(true);
                $user->addPassword($password);
            } else {
                $user->getService($serviceName)->addPassword($password);
            }
            
            $errors = $this->get('validator')->validate($user);
            if (count($errors) === 0) {
                $this->get('cloud.ldap.util.usermanipulator')->update($user);
                
                return $this->redirect($this->generateUrl('password'));
            }
        }else {
            $errors=$form->getErrors(true);
        }
        
        $this->getRequest()
            ->getSession()
            ->getFlashBag()
            ->set('errors', $this->get('twig')->render('CloudFrontBundle::error.html.twig', array(
            'errors' => $errors
        )));
        
        return $this->redirect($this->generateUrl('password'));
    }

    /**
     * @Route("/service/{serviceName}/password/{passwordID}/delete",name="password_password_delete")
     * @Route("/password/{passwordID}/delete",name="password_password_delete")
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

        return $this->redirect($this->generateUrl('password'));
    }

    /**
     * @Route("/service/{service}/edit",name="password_service_edit")
     * @Method("POST")
     * @Template()
     */
    public function serviceMasterPasswordEditAction(Request $request,$service)
    {
        $user = $this->getUser();
        $service = $user->getService($service);
        
        $form = $this->createForm(new ServiceType(), $service);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('cloud.ldap.util.usermanipulator')->update($user);
        }
        
        return $this->redirect($this->generateUrl('password'));
    }
}
