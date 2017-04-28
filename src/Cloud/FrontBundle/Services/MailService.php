<?php
namespace Cloud\FrontBundle\Services;

use Cloud\LdapBundle\Entity\User;
use Swift_Mailer;
use Twig_Environment;
use Crypt_GPG;

class MailService
{

    protected $mailer;
    protected $twig;

    public function __construct(Swift_Mailer $mailer, Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }


    public function sendToUser(User $user, $template, $context = array())
    {

        $template = $this->twig->load($template);
        dump($template);

        $subject =
            $template->renderBlock('subject',
                $context
            );
        $body_text =
            $template->renderBlock('text',
                $context
            );
        $body_html =
            $template->renderBlock('html',
                $context
            );


        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom('tuxcoder@localhost')
            ->setTo('tuxcoder@localhost');


        if($user->getGpgPublicKey()) {
            $gpg = new Crypt_GPG();
            $key=$gpg->importKey($user->getGpgPublicKey());
            $gpg->addEncryptKey($key['fingerprint']);
            $body = $gpg->encrypt($body_text);

            $message
                ->setEncoder(\Swift_DependencyContainer::getInstance()->lookup('mime.rawcontentencoder'))
                ->setBody($body,'application/pgp-encrypted')
                ;
        }else {

            $message->setBody($body_text,'text/plain')
                    ->addPart($body_html,'text/html');
        }
            ;
        dump($message);
        $this->mailer->send($message);
    }
}