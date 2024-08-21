<?php

namespace App\Form\Type;

use App\Entity\Country;
use App\Entity\Member;
use App\Entity\PoliticalGroup;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberMapType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('country', EntityType::class, [
                'label' => 'country',
                'class' => Country::class,
                'placeholder' => 'choice_country',
                'choice_label' => 'label',
                'required' => false,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er
                        ->createQueryBuilder('country')
                        ->orderBy('country.label', 'ASC')
                    ;
                }
            ])
            ->add('group', EntityType::class, [
                'label' => 'political_group',
                'class' => PoliticalGroup::class,
                'placeholder' => 'choice_group',
                'choice_label' => 'label',
                'required' => false,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er
                        ->createQueryBuilder('political_group')
                        ->orderBy('political_group.label', 'ASC')
                    ;
                }
            ])
            ->add('mapType', ChoiceType::class, [
                'label' => 'comparison_type',
                'choices' => ['matrix_type.political_group' => 1, 'matrix_type.country' => 2],
                'expanded' => true,
                'choice_attr' => [
                    'matrix_type.political_group' => ['data-target' => 'data-group'],
                    'matrix_type.country' => ['data-target' => 'data-country'],
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('method', 'GET');
        $resolver->setDefault('csrf_protection', false);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
