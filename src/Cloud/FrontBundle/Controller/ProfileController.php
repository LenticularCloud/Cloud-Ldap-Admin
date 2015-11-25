<?php

namespace Cloud\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/profile")
 *
 */
class ProfileController extends Controller
{
    
    /**
     * @Route("/",name="profile")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
