<?php

namespace Cloud\RegistrationBundle\Controller;

use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Security\CryptEncoder;
use Cloud\RegistrationBundle\Entity\User;
use Cloud\RegistrationBundle\Form\Type\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    /**
     * @Route("/",name="registration_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(RegistrationType::class, new User(), array(
            'action' => $this->generateUrl('registraion_do')
        ));

        return ['registration' => $form->createView()];
    }


    /**
     * @param   Request $request
     * @return  Response
     * @Route("/do",name="registraion_do")
     */
    public function registrationAction(Request $request)
    {
        $response = new Response();
        $form = $this->createForm(RegistrationType::class, new User());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $user = $form->getData();
            if (in_array(strtolower($user->getUsername()), $this->get('cloud.ldap.userprovider')->getUsernames()) ||
                $em->getRepository('CloudRegistrationBundle:User')->findOneByUsername($user->getUsername())
            ) {
                return $response->setContent(json_encode(['successfully' => false, 'errors' => ['message' => 'user exiests']]));
            }

            $password = new Password();
            $password->setMasterPassword(true);
            $password->setPasswordPlain($user->getPassword());
            $encoder = new CryptEncoder();
            $encoder->encodePassword($password);
            $user->setPasswordHash($password->getHash());
            $em->persist($user);
            $em->flush();
        } else {
            return $response->setContent(json_encode(['successfully' => false, 'errors' => ['message' => $form->getErrors(true)->__toString()]]));
        }

        return $response->setContent(json_encode(['successfully' => true, 'message' => 'Your account need to get activated by a admin']));
    }
}
