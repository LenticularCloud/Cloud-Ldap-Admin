<?php
namespace Cloud\FrontBundle\Services;

use Cloud\LdapBundle\Entity\User;
use Monolog\Logger;
use Swift_Mailer;
use Swift_Message;
use Swift_Attachment;
use Twig_Environment;
use gnupg;

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
    public function sendToUser(User $user, $template, $context = array(), $alt_address = false)
    {

        $template = $this->twig->load($template);

        $subject = $template->renderBlock('subject', $context);
        $body_text = $template->renderBlock('text', $context);
        $body_html = $template->renderBlock('html', $context);


        $message = new Swift_Message();
        $message
            ->setSubject($subject)
            ->setFrom($this->mailer_from);
        if($alt_address) {
            $message->setTo($user->getAltEmail());
        } else {
            $message->setTo($user->getEmail());
        }

        if ($user->getGpgPublicKey()) {
            try {
                $gnupg =new gnupg();
                $key = $gnupg->import($user->getGpgPublicKey());
                $gnupg->addencryptkey($key['fingerprint']);
                $keyinfo = $gnupg->keyinfo($key['fingerprint'])[0];

                if($keyinfo['disabled'] || $keyinfo['expired'] || $keyinfo['revoked']) {
                    throw new \Exception('key disabled, expired or revoked');
                }

                $body = $gnupg->encrypt($body_text);
                $message->setBody('Version 1', 'application/encrypted');
                $message->attach(
                    (new Swift_Attachment($body))
                        ->setDisposition('inline')
            		  	->setFilename('encrypted.asc')
              			->setContentType('application/octet-stream')
              			->setDescription('OpenPGP encrypted message')
              			->setEncoder(new \Swift_Mime_ContentEncoder_PlainContentEncoder('7bit')));
            }catch (\Exception $e) {
                $this->logger->error("can't send gpg message ".$e->getMessage().$e->getTraceAsString());
                throw $e;
            }
        } else {

            $message->setBody($body_text, 'text/plain')
                ->addPart($body_html, 'text/html');
        }
        $this->mailer->send($message);
        return true;
    }
}
