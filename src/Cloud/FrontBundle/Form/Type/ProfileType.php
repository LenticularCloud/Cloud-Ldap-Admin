<?php
namespace Cloud\FrontBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotBlankValidator;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\FileValidator;
use Symfony\Component\Form\CallbackTransformer;
use Cloud\LdapBundle\Entity\Service;

class ProfileType extends AbstractType
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
            ->add('email','email',['required'=>false])
            ->add('altEmail','email',['required'=>false])
            ->add('givenName','text',['required'=>false])
            ->add('sureName','text',['required'=>false])
            ->add('displayName','text')
            ->add('save', 'submit');
    }

    public function getName()
    {
        return 'profile';
    }
}