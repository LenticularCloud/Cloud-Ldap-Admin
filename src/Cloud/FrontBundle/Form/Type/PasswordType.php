<?php
namespace Cloud\FrontBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotBlankValidator;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\FileValidator;
use Symfony\Component\Form\CallbackTransformer;
use Cloud\LdapBundle\Entity\Service;

class PasswordType extends AbstractType
{

    public function __construct()
    {
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cloud\LdapBundle\Entity\Password',
            'validation_groups' => array(
                'Default'
            )
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id','text')
            ->add('id_old','hidden',array('mapped'=>false))
            ->add('passwordPlain','repeated',array(
                'type'=>'password',
                'required' => false,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ->add('save', 'submit',array('label'=>'save','attr'=>['class'=>'btn-primary']))
            ->add('remove', 'submit',array('label'=>'remove','attr'=>['class'=>'btn-danger']));
    }

    public function getName()
    {
        return 'password';
    }
}