<?php
namespace Cloud\FrontBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminUserType extends AbstractType
{

    public function __construct()
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cloud\LdapBundle\Entity\User',
            'validation_groups' => array(
                'Default'
            )
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('roles', Type\ChoiceType::class, array(
                'choices' => array('ROLE_ADMIN'=>'ROLE_ADMIN', 'ROLE_ADMIN_REG'=>'ROLE_ADMIN_REG', 'ROLE_USER'=>'ROLE_USER'),
                'multiple'=>true,
                'required' => false,
            ))
            ->add('email', Type\EmailType::class, array('required' => false))
            ->add('altEmail', Type\EmailType::class, array('required' => false))
            ->add('givenName', Type\TextType::class, array('required' => false))
            ->add('sureName', Type\TextType::class, array('required' => false))
            ->add('displayName', Type\TextType::class, array('required' => false))
            ->add('gpgPublicKey', Type\TextareaType::class, array('required' => false,'disabled'=>true));
    }
}