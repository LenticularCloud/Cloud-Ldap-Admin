<?php

namespace Cloud\FrontBundle\Controller;

use Cloud\FrontBundle\Form\Type\AdminUserType;
use Cloud\FrontBundle\Form\Type\AdminGroupType;
use Cloud\FrontBundle\Form\Type\PasswordType;
use Cloud\LdapBundle\Entity\Password;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        return [
            'users' => $this->get('cloud.ldap.userprovider')->getUsers(),
            'groups' => $this->get('cloud.ldap.groupprovider')->getGroups(),
        ];
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
        $form_user = $this->createForm(AdminUserType::class, $user, [
            'action' => $this->generateUrl('admin_user_edit', ['username' => $username]),
            'method' => 'POST',
        ]);
        $form_user->add('save', SubmitType::class, array(
            'label' => 'save',
            'attr' => ['class' => 'btn-primary'],
        ));

        $form_password = $this->createForm(PasswordType::class, $user->getPasswordObject(), [
            'action' => $this->generateUrl('admin_user_edit_pw', ['username' => $username]),
            'method' => 'POST',
        ]);
        $form_password->add('save', SubmitType::class, array(
            'label' => 'save',
            'attr' => ['class' => 'btn-primary'],
        ));

        return array(
            'form_user' => $form_user->createView(),
            'form_password' => $form_password->createView()
        );
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
        $response = new Response();
        $response->headers->set('Content-Type', 'text/javascript');

        $form_user = $this->createForm(AdminUserType::class, $user);

        $form_user->add('save', SubmitType::class, array(
            'label' => 'save',
            'attr' => ['class' => 'btn-primary'],
        ));

        $form_user->handleRequest($request);
        $this->get('cloud.ldap.util.usermanipulator')->update($user);

        $response->setContent(json_encode(['successfully' => true]));

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

        $form_password = $this->createForm(PasswordType::class, $user->getPasswordObject());
        $form_password->add('save', SubmitType::class, array(
            'label' => 'save',
            'attr' => ['class' => 'btn-primary'],
        ));

        $form_password->handleRequest($request);

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
        $response = new Response();
        $response->headers->set('Content-Type', 'text/javascript');

        $this->get('cloud.ldap.util.usermanipulator')->delete($user);

        $response->setContent(json_encode(['successfully' => true]));

        return $response;
    }


    /**
     * @Route("/group/{name}",name="admin_group")
     * @Template()
     */
    public function groupAction(Request $request, $name)
    {
        $group = $this->get('cloud.ldap.groupprovider')->loadGroupByName($name);
        if ($group === null) {
            throw $this->createNotFoundException('The group does not exist');
        }
        $form_user = $this->createForm(AdminGroupType::class, $group, [
            'action' => $this->generateUrl('admin_group_edit', ['name' => $name]),
            'method' => 'POST',
        ]);
        $form_user->add('save', SubmitType::class, array(
            'label' => 'save',
            'attr' => ['class' => 'btn-primary'],
        ));

        return array(
            'form_group' => $form_user->createView()
        );
    }


    /**
     * @Route("/group/{name}/edit",name="admin_group_edit")
     */
    public function groupEditAction(Request $request, $name)
    {
        $group = $this->get('cloud.ldap.groupprovider')->loadGroupByName($name);
        if ($group === null) {
            throw $this->createNotFoundException('The user does not exist');
        }
        $response = new Response();
        $response->headers->set('Content-Type', 'text/javascript');

        $form_group = $this->createForm(AdminGroupType::class, $group);

        $form_group->add('save', SubmitType::class, array(
            'label' => 'save',
            'attr' => ['class' => 'btn-primary'],
        ));

        $form_group->handleRequest($request);
        $this->get('cloud.ldap.util.groupmanipulator')->update($group);

        $response->setContent(json_encode(['successfully' => true]));

        return $response;
    }


    /**
     * @Route("/group/{name}/delete",name="admin_group_delete")
     *
     * @param   $request    Request
     * @param   $name   string
     * @return Response
     */
    public function groupDeleteAction(Request $request, $name)
    {
        //@TODO
        return [];
        $user = $this->get('cloud.ldap.groupprovider')->loadGroupByName($name);
        if ($user === null) {
            throw $this->createNotFoundException('The user does not exist');
        }
        $response = new Response();
        $response->headers->set('Content-Type', 'text/javascript');

        $this->get('cloud.ldap.util.usermanipulator')->delete($user);

        $response->setContent(json_encode(['successfully' => true]));

        return $response;
    }
}
