<?php
namespace Cloud\FrontBundle\Form\Type;

use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordResetType extends AbstractType
{

    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username_email', Type\TextType::class, array('required' => true, 'label'=>'Username'))
            ->add('captcha', CaptchaType::class)
        ;
    }
}