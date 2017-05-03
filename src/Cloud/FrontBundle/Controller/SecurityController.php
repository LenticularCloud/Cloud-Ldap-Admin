<?php

namespace Cloud\FrontBundle\Controller;

use Cloud\FrontBundle\Form\Type\PasswordResetType;
use Cloud\FrontBundle\Form\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return
            [
                'last_username' => $lastUsername,
                'error' => $error,
            ];
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request)
    {
        $this->get('security.token_storage')->setToken(null);
        $this->get('request')->getSession()->invalidate();

        return $this->redirect($this->generateUrl('login'));
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction(Request $request)
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }

    /**
     * @Route("/password_reset",name="password_reset")
     * @Method("get")
     * @Template()
     */
    public function resetPasswordAction()
    {
        $form = $this->createForm(PasswordResetType::class, null, [
            'action' => $this->generateUrl('password_reset_send'),
            'method' => 'POST',
        ]);
        $form->add('save', SubmitType::class, array(
            'label' => 'reset password',
            'attr' => ['class' => 'btn-primary'],
        ));

        return array('form_password_reset' => $form->createView());
    }

    /**
     * @Route("/password_reset",name="password_reset_send")
     * @Method("post")
     */
    public function resetPasswordSendAction(Request $request)
    {
        $form = $this->createForm(PasswordResetType::class);
        $form->add('save', SubmitType::class, array(
            'label' => 'reset password',
            'attr' => ['class' => 'btn-primary'],
        ));
        $form->handleRequest($request);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/javascript');

        $errors = $form->getErrors(true);
        if (count($errors) === 0) {
            $username = $form->getData()['username_email'];
            $user = $this->get('cloud.ldap.userprovider')->loadUserByUsername($username);

            $passwordTokenService = $this->get('cloud.front.passwordreset');
            $token = $passwordTokenService->generateToken($user);

            $mailer = $this->get('cloud.front.mailer');
            $mailer->sendToUser($user, 'CloudFrontBundle:Emails:password_reset.html.twig', array(
                'username' => $user->getUsername(),
                'reset_url' => $this->generateUrl('password_reset_do',
                    ['username' => $user->getUsername(), 'token' => $token],UrlGeneratorInterface::ABSOLUTE_URL),
            ));

            $data = array(
                'successfully' => true,
                'msg' => 'please check your mail',
            );
        } else {
            $errorMsgs = array();
            foreach ($errors as $error) {
                $errorMsgs[] = $error->getMessage();
            }
            $data = array(
                'successfully' => false,
                'errors' => $errorMsgs,
            );
        }

        $response->setContent(json_encode($data));

        return $response;
    }

    /**
     * @Route("/password_reset/{username}/{token}",name="password_reset_do")
     * @Template()
     */
    public function resetPasswordDoAction(Request $request, $username, $token)
    {
        $passwordTokenService = $this->get('cloud.front.passwordreset');
        $user = $this->get('cloud.ldap.userprovider')->loadUserByUsername($username);
        $form = $this->createForm(PasswordType::class,$user->getPasswordObject());
        $form->add('save', SubmitType::class, array(
            'label' => 'reset password',
            'attr' => ['class' => 'btn-primary'],
        ));
        $form->handleRequest($request);

        if ($user === null || !$passwordTokenService->validateToken($user, $token)) {
            return ['user'=> null];
        }

        if($form->isValid()) {
            $this->get('cloud.ldap.util.usermanipulator')->update($user);
            return $this->redirectToRoute('login');
        }else {
            return ['user'=> $user, 'form_password_reset_do' => $form->createView()];
        }
    }
}