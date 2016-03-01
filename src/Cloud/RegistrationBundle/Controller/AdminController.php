<?php

namespace Cloud\RegistrationBundle\Controller;

use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Entity\User;
use Cloud\RegistrationBundle\Entity\User as RegUser;
use Cloud\RegistrationBundle\Form\Type\EditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 * @Security("has_role('ROLE_ADMIN_REG')")
 */
class AdminController extends Controller
{
    /**
     * @Route("/",name="registration_admin_index")
     * @Template()
     */
    public function indexAction()
    {
        $users = $this->getDoctrine()->getManager()->getRepository("CloudRegistrationBundle:User")->findAll();
        $form = $this->createForm(EditType::class);

        return ['users' => $users, 'form_edit' => $form->createView()];
    }

    /**
     * @Route("/edit/{user}",name="registration_admin_edit")
     */
    public function editAction(Request $request, RegUser $user)
    {
        $response = new Response();
        $form=$this->createForm(EditType::class);
        $form->handleRequest($request);

        if($form->isValid()) {
            $data=$form->getData();
            $em=$this->getDoctrine()->getManager();
            if($data['action'] ===true ) {
                $userLdap=$this->get('cloud.ldap.util.usermanipulator')->createUser($user->getUsername());

                $password=new Password();
                $password->setHash($user->getPasswordHash());
                $password->setId('default');
                $userLdap->addPassword($password);

                $userLdap->setAltEmail($user->getAltEmail());
                $this->get('cloud.ldap.util.usermanipulator')->create($userLdap);
                $em->remove($user);

            }elseif($data['action'] ===false ) {
                $em->remove($user);
            }else {
                $response->setStatusCode(400);
                return $response;
            }
            $em->flush();
        }

        return $response;
    }
}