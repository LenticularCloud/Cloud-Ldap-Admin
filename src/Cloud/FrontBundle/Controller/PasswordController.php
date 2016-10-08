<?php
namespace Cloud\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cloud\FrontBundle\Form\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Cloud\FrontBundle\Form\Type\ServiceType;
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
    public function indexAction(Request $request)
    {


        //---- master ---
        $formsEntity = [];
        foreach ($this->get('cloud.front.formgenerator')->getUserForms() as $typeName => $type) {
            $formsEntity[] = $this->createForm($type, $this->getUser(), array(
                'action' => $this->generateUrl('password_edit', array('type' => $typeName)),
                'method' => 'POST',
            ))->createView();
        }

        //--- services ---
        $formsServices = array();
        foreach ($this->getUser()->getServices() as $service) {
            //-- service settings --
            $formsServices[$service->getName()] = array();
            $formsServices[$service->getName()][] = $this->createForm(new ServiceType(), $service, array(
                'action' => $this->generateUrl('password_service_edit', array(
                    'type' => 'status',
                    'serviceName' => $service->getName(),
                )),
                'method' => 'POST',
            ))->createView();

            if (!$service->isEnabled()) {
                continue;
            }

            foreach ($this->get('cloud.front.formgenerator')->getServiceForm($service->getName()) as $typeName => $type) {

                $formsServices[$service->getName()][] = $this->createForm($type, $service, array(
                    'action' => $this->generateUrl('password_service_edit', array(
                        'type' => $typeName,
                        'serviceName' => $service->getName(),
                    )),
                    'method' => 'POST',
                ))->createView();
            }
            /*
            if (count($service->getPasswords()) < $service->maxPasswords()) {
                $newPassword = new Password();
                $formEdit[$service->getName()][] = $this->createForm(new NewPasswordType(), $newPassword, array(
                    'action' => $this->generateUrl('password_service_password_new', array(
                        'serviceName' => $service->getName()
                    )),
                    'method' => 'POST'
                ))
                    ->createView();
            }*/
        }


        $errors = $request
            ->getSession()
            ->getFlashBag()
            ->get('errors', array());

        return array(
            'errors' => $errors,
            'formsEntity' => $formsEntity,
            'formsServices' => $formsServices,
        );
    }


    /**
     * @Route("/{type}/edit",name="password_edit")
     * @Route("/{serviceName}/{type}/edit",name="password_service_edit")
     * @Method("POST")
     */
    public function genericFormAction(Request $request, $type, $serviceName = null)
    {

        $response = new Response();
        $user = $this->getUser();

        //@TODO check if service exist

        if ($serviceName === null) {
            $formsType = $this->get('cloud.front.formgenerator')->getUserForms();
            $form = $this->createForm($formsType[$type], $this->getUser());
        } else {
            $formsType = $this->get('cloud.front.formgenerator')->getServiceForm($serviceName);
            $form = $this->createForm($formsType[$type], $this->getUser()->getServices()[$serviceName]);
        }

        $form->handleRequest($request);

        $errors = $this->get('validator')->validate($user);
        dump($form,$errors);

        if (count($errors) === 0) {
            $this->get('cloud.ldap.util.usermanipulator')->update($user);
            $data = array(
                'successfully' => true
            );
        } else {
            $errorMsgs = array();
            foreach($errors as $error) {
                $errorMsgs[] = $error->getMessage();
            }
            $data = array(
                'successfully' => false,
                'errors' => $errorMsgs,
            );
        }

        $response->setContent(json_encode($data));

        return $response;
    }

    /**
     * @Method("POST")
     * @Template()
     */
    public function passwordEditAction(Request $request, $passwordId, $serviceName = null)
    {
        $user = $this->getUser();

        if ($serviceName === null) {
            $password = $user->getPassword($passwordId);
        } else {
            $password = $user->getService($serviceName)->getPassword($passwordId);
        }

        $form = $this->createForm(new PasswordType(), $password);
        $form->handleRequest($request);

        if ($form->get('remove')->isClicked()) {
            if ($serviceName == null) {
                $user->removePassword($password);
            } else {
                $user->getService($serviceName)->removePassword($password);
            }
        } else {
            if ($serviceName == null) {
                $user->addPassword($password);
            } else {
                $user->getService($serviceName)->addPassword($password);
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
                    'errors' => $errors,
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
                'errors' => $errors,
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
