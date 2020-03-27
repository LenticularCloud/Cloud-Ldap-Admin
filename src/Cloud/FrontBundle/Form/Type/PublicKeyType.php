<?php
namespace Cloud\FrontBundle\Form\Type;

use Cloud\LdapBundle\Entity\PosixService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicKeyType extends AbstractType
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
            ->add('ssh_public_key', Type\CollectionType::class, array(
                'entry_type' => Type\TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ));
    }
}