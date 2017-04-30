<?php
namespace Cloud\FrontBundle\Services;

use Cloud\LdapBundle\Entity\User;
use Monolog\Logger;
use Swift_Mailer;
use Twig_Environment;
use Crypt_GPG;

class MailService
{

    protected $mailer;
    protected $twig;
    protected $logger;
    protected $mailer_from;

    /**
     * MailService constructor.
     * @param Swift_Mailer     $mailer
     * @param Twig_Environment $twig
     * @param Logger           $logger
     * @param                  $mailer_from
     */
    public function __construct(Swift_Mailer $mailer, Twig_Environment $twig, Logger $logger, $mailer_from)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->mailer_from = $mailer_from;
    }


    /**
     * @param User  $user
     * @param string $template
     * @param array $context
     * @throws \Exception
     * @throws \Throwable
     * @return boolean
     */
    public function sendToUser(User $user, $template, $context = array())
    {

        $template = $this->twig->load($template);

        $subject = $template->renderBlock('subject', $context);
        $body_text = $template->renderBlock('text', $context);
        $body_html = $template->renderBlock('html', $context);


        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->mailer_from)
            ->setTo($user->getEmail());


        if ($user->getGpgPublicKey()) {
            try {
                $gpg = new Crypt_GPG();
                $key = $gpg->importKey($user->getGpgPublicKey());
                $gpg->addEncryptKey($key['fingerprint']);
                $body = $gpg->encrypt($body_text);

                $message
                    ->setEncoder(\Swift_DependencyContainer::getInstance()->lookup('mime.rawcontentencoder'))
                    ->setBody($body, 'application/pgp-encrypted');
            }catch (\Exception $e) {
                $this->logger->error("can't send gpg message ".$e->getMessage().$e->getTraceAsString());
                return false;
            }
        } else {

            $message->setBody($body_text, 'text/plain')
                ->addPart($body_html, 'text/html');
        }
        $this->mailer->send($message);
        return true;
    }
}