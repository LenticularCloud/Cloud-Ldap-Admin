<?php
namespace Cloud\FrontBundle\Form\Type;

use Cloud\LdapBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{

    public function __construct()
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'validation_groups' => array(
                'Default',
            ),
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', Type\EmailType::class, array('required' => false, 'disabled' => true))
            ->add('altEmail', Type\EmailType::class, array('required' => false))
            ->add('givenName', Type\TextType::class, array('required' => false))
            ->add('surName', Type\TextType::class, array('required' => false))
            ->add('displayName', Type\TextType::class)
            ->add('gpgPublicKey', Type\TextareaType::class, array('required' => false, 'attr' => array('class' => 'gpgpublickey')));
    }
}
