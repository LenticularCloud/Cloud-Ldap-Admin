<?php
namespace Cloud\FrontBundle\Form\Type;

use Cloud\LdapBundle\Entity\Password;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordType extends AbstractType
{
    protected $removeAble;

    public function __construct($removeAble = true)
    {
        $this->removeAble = $removeAble;
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
            ->add('id', Type\TextType::class)
            ->add('passwordPlain', Type\RepeatedType::class, array(
                'type' => Type\PasswordType::class,
                'required' => false,
                'first_options' => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ));
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        // make setted ids readonly to prevent change of them
        $id = $view->children['id']->vars['value'];
        if($id !== '') {
            $view->children['id']->vars['attr']['readonly'] = 'readonly';
        }
    }

    public function getName()
    {
        return 'password_object';
    }
}