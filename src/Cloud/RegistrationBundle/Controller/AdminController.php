<?php

namespace Cloud\RegistrationBundle\Controller;

use Cloud\LdapBundle\Entity\Password;
use Cloud\LdapBundle\Security\CryptEncoder;
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

        $data = [];
        foreach ($users as $user) {
            $form = $this->createForm(EditType::class);
            $data[] = ['form' => $form->createView(), 'user' => $user];

        }

        return ['data' => $data];
    }

    /**
     * @Route("/edit/{user}",name="registration_admin_edit")
     */
    public function editAction(Request $request, $user)
    {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(EditType::class);
        $form->handleRequest($request);

        $user = $em->getRepository(RegUser::class)->findOneByUsername($user);

        if ($form->isValid()) {
            $data = $form->getData();
            if ($data['action'] === true) {
                $password = new Password();
                $password->setHash($user->getPasswordHash());
                $password->setId('default');
                $password->setEncoder(CryptEncoder::class);

                $userLdap = $this->get('cloud.ldap.util.usermanipulator')->createUserObject($user->getUsername(), $password);

                $userLdap->setAltEmail($user->getAltEmail());
                $userLdap->setGpgPublicKey($user->getGpgPublicKey());
                $this->get('cloud.ldap.util.usermanipulator')->create($userLdap);
                $em->remove($user);

            } elseif ($data['action'] === false) {
                $em->remove($user);
            } else {
                $response->setStatusCode(400);

                return $response;
            }
            $em->flush();
        } else {
            $response->setContent(json_encode([
                'successfully' => false,
                'error' => $form->getErrors(true)->__toString(),
            ]));

            return $response;
        }

        $response->setContent(json_encode(['successfully' => true]));

        return $response;
    }
}