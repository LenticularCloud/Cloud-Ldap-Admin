<?php

namespace Cloud\FrontBundle\Controller;

use Cloud\FrontBundle\Form\Type\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
        $form = $this->createForm(new ProfileType(), $this->getUser(), array(
            'action' => $this->generateUrl('profile_edit'),
            'method' => 'POST'
        ));

        return ['form' => $form->createView()];
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
