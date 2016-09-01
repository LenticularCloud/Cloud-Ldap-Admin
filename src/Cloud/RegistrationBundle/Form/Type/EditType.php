<?php
namespace Cloud\RegistrationBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class EditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('action', ChoiceType::class, [
            'choices' => [
                'accept' => true,
                'deny' => false],
            'required' => false,
            'choices_as_values' => true])
            ->add('submit', SubmitType::class);
    }
}