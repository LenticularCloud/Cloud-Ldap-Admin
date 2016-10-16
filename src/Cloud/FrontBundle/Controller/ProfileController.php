<?php

namespace Cloud\FrontBundle\Controller;

use Cloud\FrontBundle\Form\Type\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
        foreach ($this->get('cloud.front.formgenerator')->getUserForms() as $typeName => $type) {
            $form = $this->createForm($type, $this->getUser(), array(
                'action' => $this->generateUrl('profile_edit', array('type' => $typeName)),
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
     * @Route("/edit",name="profile_edit")
     *
     * @param $request Request
     * @return Response
     */
    public function editAction(Request $request)
    {
        $response = new Response();
        $form = $this->createForm(new ProfileType(), $this->getUser());

        // workaround to premit message from symfony 'This form should not contain extra fields.'
        $form->add('save', SubmitType::class, array(
            'label' => 'save',
            'attr' => ['class' => 'btn-primary'],
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('cloud.ldap.util.usermanipulator')->update($this->getUser());
            $response->setContent(json_encode(['successfully' => true]));
        } else {
            $response->setContent(json_encode(['successfully' => false]));
        }

        return $response;
    }
}
