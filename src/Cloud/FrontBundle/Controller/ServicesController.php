<?php
namespace Cloud\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Cloud\FrontBundle\Form\Type\ServiceType;

/**
 * @Route("/services")
 */
class ServicesController extends Controller
{

    /**
     * @Route("/",name="services")
     * @Template()
     *
     * @param   Request   $request
     * @return array
     */
    public function indexAction(Request $request)
    {
        $formsServices = array();
        foreach ($this->getUser()->getServices() as $service) {

            $formsServices[$service->getName()] = array();

            if (!$service->isEnabled()) {
                //-- service settings --
                $form = $this->createForm(ServiceType::class, $service, array(
                    'action' => $this->generateUrl('services_edit', array(
                        'type' => ServiceType::class,
                        'serviceName' => $service->getName(),
                    )),
                    'method' => 'POST',
                ));
                $form->add('save', SubmitType::class, array(
                    'label' => 'save',
                    'attr' => ['class' => 'btn-primary'],
                ));

                $formsServices[$service->getName()][] = $form->createView();

                // skip other form if service is disabled
                continue;
            }

            foreach ($this->get('cloud.front.formgenerator')->getServiceForms($service->getName())  as $type) {

                $form = $this->createForm($type, $service, array(
                    'action' => $this->generateUrl('services_edit', array(
                        'type' => $type,
                        'serviceName' => $service->getName(),
                    )),
                    'method' => 'POST',
                ));
                $form->add('save', SubmitType::class, array(
                    'label' => 'save',
                    'attr' => ['class' => 'btn-primary'],
                ));

                $formsServices[$service->getName()][] = $form->createView();

            }
        }

        $errors = $request
            ->getSession()
            ->getFlashBag()
            ->get('errors', array());

        return array(
            'errors' => $errors,
            'formsServices' => $formsServices,
        );
    }


    /**
     * @Route("/{serviceName}/{type}/edit",name="services_edit",methods={"POST"})
     *
     * @param   Request     $request
     * @param   string      $type
     * @param   string      $serviceName
     * @return array
     */
    public function genericFormAction(Request $request, $type, $serviceName)
    {

        $response = new Response();
        $user = $this->getUser();

        //@TODO check if service exist

        $formsType = $this->get('cloud.front.formgenerator')->getServiceForms($serviceName);
        if(!in_array($type, $formsType)){
            //error
            die('error');
        }
        $form = $this->createForm($type, $this->getUser()->getServices()[$serviceName]);

        // workaround to premit message from symfony 'This form should not contain extra fields.'
        $form->add('save', SubmitType::class, array(
            'label' => 'save',
            'attr' => ['class' => 'btn-primary'],
        ));

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
