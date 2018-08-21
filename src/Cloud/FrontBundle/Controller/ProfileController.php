<?php

namespace Cloud\FrontBundle\Controller;

use Cloud\FrontBundle\Form\Type\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $forms = [];
        foreach ($this->get('cloud.front.formgenerator')->getUserForms() as $type) {
            $form = $this->createForm($type, $this->getUser(), array(
                'action' => $this->generateUrl('profile_edit', array('type' => $type)),
                'method' => 'POST',
            ));

            $form->add('save', SubmitType::class, array(
                'label' => 'save',
                'attr' => ['class' => 'btn-primary'],
            ));
            $forms[] = $form->createView();
        }

        return ['forms' => $forms];
    }

    /**
     * @Route("/edit/{type}",name="profile_edit",methods={"POST"})
     *
     * @param $request Request
     * @return Response
     */
    public function editAction(Request $request, $type)
    {
        $response = new Response();
        $formsType = $this->get('cloud.front.formgenerator')->getUserForms();
        if(!in_array($type, $formsType)){
            //error
            die('error');
        }
        $form = $this->createForm($type, $this->getUser());

        // workaround to premit message from symfony 'This form should not contain extra fields.'
        $form->add('save', SubmitType::class, array(
            'label' => 'save',
            'attr' => ['class' => 'btn-primary'],
        ));

        $form->handleRequest($request);
        $errors = $form->getErrors(true);

        if (count($errors) === 0) {
            $this->get('cloud.ldap.util.usermanipulator')->update($this->getUser());
            $response->setContent(json_encode(['successfully' => true]));
        } else {
            $errorMsgs = array();
            foreach ($errors as $error) {
                $errorMsgs[] = $error->getMessage();
            }
            $response->setContent(json_encode(['successfully' => false, 'msg'=> $errorMsgs]));
        }

        return $response;
    }
}
