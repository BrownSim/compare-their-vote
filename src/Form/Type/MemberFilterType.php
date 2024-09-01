<?php

namespace App\Form\Type;

use App\Entity\Country;
use App\Entity\Party;
use App\Entity\PoliticalGroup;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('country', EntityType::class, [
                'label' => 'filter.country',
                'class' => Country::class,
                'required' => false,
                'choice_label' => 'label',
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('c')
                        ->innerJoin('c.members', 'members')
                        ->orderBy('c.label', 'ASC');
                },
                'choice_attr' => function (Country $country) {
                    return ['data-mp-country' => $country->getCode()];
                },
            ])
            ->add('group', EntityType::class, [
                'label' => 'filter.group',
                'class' => PoliticalGroup::class,
                'required' => false,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('g')
                        ->orderBy('g.label', 'ASC');
                },
                'choice_label' => 'label',
                'choice_attr' => function (PoliticalGroup $group) {
                    return ['data-mp-group' => $group->getCode()];
                },
            ])
            ->add('party', EntityType::class, [
                'label' => 'filter.party',
                'class' => Party::class,
                'required' => false,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('g')
                        ->join('g.country', 'country')
                        ->orderBy('country.label', 'ASC');
                },
                'group_by' => function (Party $party) {
                    return $party->getCountry()->getLabel();
                },
                'choice_label' => 'label',
                'choice_attr' => function (Party $country) {
                    return [
                        'data-mp-party' => $country->getId()
                    ];
                },
            ])
            ->add('mp_status', ChoiceType::class, [
                'label' => 'filter.mp_status',
                'choices' => [
                    'mp_status_list.all' => 0,
                    'mp_status_list.retired' => 2,
                    'mp_status_list.active' => 1,
                ],
                'choice_attr' => [
                    'mp_status_list.all' => [],
                    'mp_status_list.retired' => ['data-mp-status' => 2],
                    'mp_status_list.active' => ['data-mp-status' => 1],
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
