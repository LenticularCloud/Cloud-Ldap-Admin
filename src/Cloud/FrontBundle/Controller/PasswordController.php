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
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/password")
 */
class PasswordController extends Controller
{

    /**
     * @Route("/",name="password")
     * @Template()
     */
    public function indexAction(Request $request)
    {

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

        $formWifiPassword = $this->createForm(new PasswordType(), $this->getUser()->getNtPassword(), array(
            'action' => $this->generateUrl('password_password_edit', array(
                'serviceName' => 'wifi',
                'passwordId' => 'default'
            )),
            'method' => 'POST'
        ))->createView();


        $errors = $request
            ->getSession()
            ->getFlashBag()
            ->get('errors', array());

        return array(
            'errors' => $errors,
            'formEditMasterPasswords' => $formEditMaster,
            'formWifiPassword' => $formWifiPassword,
        );
    }

    /**
     * @Route("/service/{serviceName}/password/{passwordId}/edit",name="password_service_password_edit")
     * @Route("/password/{passwordId}/edit",name="password_password_edit")
     * @Method("POST")
     * @Template()
     */
    public function passwordEditAction(Request $request,$passwordId, $serviceName = null)
    {
        $user = $this->getUser();

        if ($serviceName === null) {
            $password = $user->getPassword($passwordId);
        } else {
            $password = $this->getUser()->getNtPassword();
            if($password===null) {
                $password= new Password();
                $this->getUser()->setNtPassword();
            }
        }

        $form = $this->createForm(new PasswordType(), $password);
        $form->handleRequest($request);

        if(!$form->isValid()) {
            $errors=$this->render('CloudFrontBundle::error.html.twig', array(
                'errors' => $form->getErrors(true)
            ));
            return new Response($errors);
        }

        if ($form->get('remove')->isClicked()) {
            switch($serviceName) {
                case 'wifi':
                    $user->setNtPassword($password);
                    break;
                default:
                    $user->removePassword($password);
            }
        }else {
            call_user_func($password->getEncoder() . '::encodePassword',[$password]);
            switch($serviceName) {
                case 'wifi':
                    $this->getUser()->setNtPassword($password);
                    break;
                default:
                    $user->addPassword($password);
            }
        }

        $errors = $this->get('validator')->validate($user);

        if (count($errors) === 0) {
            $this->get('cloud.ldap.util.usermanipulator')->update($user);
        } else {
            $request
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
            } else {
                $user->getService($serviceName)->getPassword($password->getId());
            }
            $form->addError(new FormError("Password Id is in use"));
        } catch (InvalidArgumentException $e) {
        }

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
        } else {
            $errors = $form->getErrors(true);
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
    public function serviceMasterPasswordEditAction(Request $request, $service)
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
