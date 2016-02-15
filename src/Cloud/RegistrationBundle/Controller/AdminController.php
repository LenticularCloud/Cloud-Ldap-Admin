<?php

namespace Cloud\RegistrationBundle\Controller;

use Cloud\RegistrationBundle\Form\Type\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/",name="registration_admin_index")
     */
    public function indexAction()
    {
        $users=$this->getDoctrine()->getManager()->getRepository("CloudRegistrationBundle:User")->findAll();

        return ['users'=>$users];
    }
}