<?php
namespace Cloud\FrontBundle\Form\Type;

use Cloud\LdapBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserType extends AbstractType
{

    public function __construct()
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'validation_groups' => array(
                'Default'
            )
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', Type\TextType::class, array('disabled' => true))
            ->add(
                $builder ->create('password_object', PasswordType::class)
            )
            ->add('save', Type\SubmitType::class, array('label' => 'save', 'attr' => ['class' => 'btn-primary']));
    }

    public function getName()
    {
        return 'UserType';
    }
}