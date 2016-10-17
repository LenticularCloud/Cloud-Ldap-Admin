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
            ->add('roles', Type\ChoiceType::class, [
                'choices' => ['ROLE_ADMIN'=>'ROLE_ADMIN', 'ROLE_ADMIN_REG'=>'ROLE_ADMIN_REG', 'ROLE_USER'=>'ROLE_USER'],
                'multiple'=>true,
            ])
            ->add('email', Type\EmailType::class, ['required' => false])
            ->add('altEmail', Type\EmailType::class, ['required' => false])
            ->add('givenName', Type\TextType::class, ['required' => false])
            ->add('sureName', Type\TextType::class, ['required' => false])
            ->add('displayName', Type\TextType::class, ['required' => false]);
    }
}