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

            $formsServices[$service->getName()] = array();

            if (!$service->isEnabled()) {
                //-- service settings --
                $formsServices[$service->getName()][] = $this->createForm(new ServiceType(), $service, array(
                    'action' => $this->generateUrl('password_service_edit', array(
                        'type' => 'status',
                        'serviceName' => $service->getName(),
                    )),
                    'method' => 'POST',
                ))->createView();

                // skip other form if service is disabled
                continue;
            }

            foreach ($this->get('cloud.front.formgenerator')->getServiceForms($service->getName()) as $typeName => $type) {

                $formsServices[$service->getName()][] = $this->createForm($type, $service, array(
                    'action' => $this->generateUrl('password_service_edit', array(
                        'type' => $typeName,
                        'serviceName' => $service->getName(),
                    )),
                    'method' => 'POST',
                ))->createView();
            }
        }
        dump($this->getUser());

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
            $formsType = $this->get('cloud.front.formgenerator')->getServiceForms($serviceName);
            $form = $this->createForm($formsType[$type], $this->getUser()->getServices()[$serviceName]);
        }

        $form->handleRequest($request);

        $errors = $this->get('validator')->validate($form);

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
}
