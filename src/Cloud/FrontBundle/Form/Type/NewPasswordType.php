<?php
namespace Cloud\FrontBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;

class NewPasswordType extends AbstractType
{

    public function __construct()
    {
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cloud\LdapBundle\Entity\Password',
            'validation_groups' => array(
                'create'
            )
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id',Type\EmailType::class)
            ->add('passwordPlain',Type\RepeatedType::class,array(
                'invalid_message' => 'The password fields must match.',
                'type'=>Type\PasswordType::class,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ->add('save', Type\SubmitType::class,array('attr'=>['class'=>'btn-success']));
    }

    public function getName()
    {
        return 'newPassword';
    }
}