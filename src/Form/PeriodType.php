<?php

namespace App\Form;

use App\Entity\Currency;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeriodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $now = new \DateTime();
        $builder
            ->setMethod('GET')
            ->add('from', DateType::class, [
                'widget' => 'choice',
                'label' => 'Период от',
                'data' => new \DateTime(),
            ])
            ->add('to', DateType::class, [
                'widget' => 'choice',
                'label' => 'Период до',
                'data' => new \DateTime(),
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Найти'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
