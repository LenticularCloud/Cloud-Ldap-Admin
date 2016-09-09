<?php

namespace Cloud\FrontBundle\Controller;

use Cloud\FrontBundle\Form\Type\AdminUserType;
use Cloud\FrontBundle\Form\Type\PasswordType;
use Cloud\LdapBundle\Entity\Password;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminController extends Controller
{
    /**
     * @Route("/",name="admin_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return ['users' => $this->get('cloud.ldap.userprovider')->getUsernames()];
    }


    /**
     * @Route("/user/{username}",name="admin_user")
     * @Template()
     */
    public function userAction(Request $request, $username)
    {

        $user = $this->get('cloud.ldap.userprovider')->loadUserByUsername($username);
        if ($user === null) {
            throw $this->createNotFoundException('The user does not exist');
        }
        $form_user = $this->createForm(AdminUserType::class, $user,[
            'action' => $this->generateUrl('admin_user_edit',['username'=>$username]),
            'method' => 'POST'
        ]);
        if(count($user->getPasswords())>0) {
            $password = current($user->getPasswords());
        }else {
            $password = new Password('master');
        }
        $form_password = $this->createForm(PasswordType::class, $password,[
            'action' => $this->generateUrl('admin_user_edit_pw',['username'=>$username]),
            'method' => 'POST'
        ]);

        return ['form_user' => $form_user->createView(), 'form_password' => $form_password->createView()];
    }


    /**
     * @Route("/user/{username}/edit",name="admin_user_edit")
     */
    public function userEditAction(Request $request, $username)
    {
        $user = $this->get('cloud.ldap.userprovider')->loadUserByUsername($username);
        if ($user === null) {
            throw $this->createNotFoundException('The user does not exist');
        }
        $response=new Response();
        $response->headers->set( 'Content-Type', 'text/javascript' );

        $form_user = $this->createForm(AdminUserType::class, $user);

        $form_user->handleRequest($request);
        $this->get('cloud.ldap.util.usermanipulator')->update($user);

        $response->setContent(json_encode(['successfully'=>true]));
        return $response;
    }


    /**
     * @Route("/user/{username}/editpw",name="admin_user_edit_pw")
     */
    public function userEditPwAction(Request $request, $username)
    {
        $user = $this->get('cloud.ldap.userprovider')->loadUserByUsername($username);
        if ($user === null) {
            throw $this->createNotFoundException('The user does not exist');
        }
        $response=new Response();
        $response->headers->set( 'Content-Type', 'text/javascript' );

        if(count($user->getPasswords())>0) {
            $password = current($user->getPasswords());
        }else {
            $password = new Password('master');
        }
        $form_password = $this->createForm(PasswordType::class, $password);
        $form_password->handleRequest($request);

        $user->addPassword($password);
        $this->get('cloud.ldap.util.usermanipulator')->update($user);

        $response->setContent(json_encode(['successfully'=>true]));
        return $response;
    }
    /**
     * @Route("/user/{username}/delete",name="admin_user_delete")
     *
     * @param   $request    Request
     * @param   $username   string
     * @return Response
     */
    public function userDeleteAction(Request $request, $username)
    {
        $user = $this->get('cloud.ldap.userprovider')->loadUserByUsername($username);
        if ($user === null) {
            throw $this->createNotFoundException('The user does not exist');
        }
        $response=new Response();
        $response->headers->set( 'Content-Type', 'text/javascript' );

        $this->get('cloud.ldap.util.usermanipulator')->delete($user);

        $response->setContent(json_encode(['successfully'=>true]));
        return $response;
    }
}
