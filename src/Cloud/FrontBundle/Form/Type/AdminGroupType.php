<?php
namespace Cloud\FrontBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminGroupType extends AbstractType
{

    public function __construct()
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cloud\LdapBundle\Entity\Group',
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
            ->add('members', Type\CollectionType::class, array(
                'type' => new Type\TextType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ));
    }
}