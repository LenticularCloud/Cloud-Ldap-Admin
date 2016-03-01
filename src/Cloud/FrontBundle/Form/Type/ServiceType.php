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

class ServiceType extends AbstractType
{

    public function __construct()
    {
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cloud\LdapBundle\Entity\AbstractService',
            'validation_groups' => array(
                'Default'
            )
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('masterPasswordEnabled','checkbox',['required'=>false])
            ->add('enabled','checkbox',['required'=>false])
            ->add('save', 'submit');
    }

    public function getName()
    {
        return 'service';
    }
}