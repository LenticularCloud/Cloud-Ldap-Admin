<?php

namespace Cloud\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/",name="index")
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('profile'));
    }
}
