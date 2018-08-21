<?php
namespace Cloud\FrontBundle\Form\Type;

use Cloud\LdapBundle\Entity\PosixService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

class PosixType extends AbstractType
{
    public function __construct()
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PosixService::class,
            'validation_groups' => array(
                'Default'
            )
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uid', Type\TextType::class, array(
                'disabled' => true,
            ))
            ->add('gid', Type\TextType::class, array(
                'disabled' => true,
            ))
            ->add('home_director', Type\TextType::class, array(
                'disabled' => true,
            ))
            ->add('login_shell', Type\TextType::class, array(
            ));

    }
}