<?php
namespace Cloud\FrontBundle\Form\Type;

use Cloud\LdapBundle\Entity\Password;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;

class PasswordType extends AbstractType
{

    public function __construct()
    {
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Password::class,
            'validation_groups' => array(
                'Default'
            )
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', Type\TextType::class, array('disabled' => true))
            ->add('id_old', Type\HiddenType::class, array('mapped' => false))
            ->add('passwordPlain', Type\RepeatedType::class, array(
                'type' => Type\PasswordType::class,
                'required' => false,
                'first_options' => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ->add('save', Type\SubmitType::class, array('label' => 'save', 'attr' => ['class' => 'btn-primary']))
            ->add('remove', Type\SubmitType::class, array('label' => 'remove', 'attr' => ['class' => 'btn-danger']));
    }

    public function getName()
    {
        return 'password_object';
    }
}