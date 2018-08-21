<?php
namespace Cloud\FrontBundle\Form\Type;

use Cloud\LdapBundle\Entity\Password;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Type for user passwords, id will not be shown
 *
 * Class PasswordType
 * @package Cloud\FrontBundle\Form\Type
 */
class PasswordType extends AbstractType
{
    public function __construct()
    {
    }

    public function configureOptions(OptionsResolver $resolver)
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
            ->add('id', Type\HiddenType::class)
            ->add('passwordPlain', Type\RepeatedType::class, array(
                'type' => Type\PasswordType::class,
                'required' => false,
                'first_options' => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
        ;
    }
}