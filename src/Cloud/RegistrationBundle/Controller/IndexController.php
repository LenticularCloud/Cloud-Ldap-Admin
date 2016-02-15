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
     * @Route("/do",name="registraion_do")
     */
    public function registrationAction(Request $request)
    {
        $response=new Response();
        $form = $this->createForm(RegistrationType::class,new User());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $password=new Password();
            $password->setPasswordPlain($user->getPassword());
            $encoder=new CryptEncoder();
            $encoder->encodePassword($password);
            $user->setPasswordHash($password->getHash());
            $em=$this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }else {
            var_dump($form->getErrors(true)->__toString());
            var_dump($form->getData());
        }
        return $response;
    }
}
