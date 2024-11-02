<?php

namespace App\Form\Type;

use App\Entity\Country;
use App\Entity\PoliticalGroup;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MemberMapType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $mpCountryOptions =  [
            'label' => 'form.filter.matrix.country.label',
            'class' => Country::class,
            'placeholder' => 'form.filter.matrix.country.placeholder',
            'choice_label' => 'label',
            'required' => false,
            'query_builder' => function (EntityRepository $er): QueryBuilder {
                return $er
                    ->createQueryBuilder('country')
                    ->innerJoin('country.members', 'members')
                    ->orderBy('country.label', 'ASC')
                ;
            }
        ];

        $groupOptions = [
            'label' => 'form.filter.matrix.group.label',
            'class' => PoliticalGroup::class,
            'placeholder' => 'form.filter.matrix.group.placeholder',
            'choice_label' => 'label',
            'required' => false,
            'query_builder' => function (EntityRepository $er): QueryBuilder {
                return $er
                    ->createQueryBuilder('political_group')
                    ->orderBy('political_group.label', 'ASC')
                ;
            }
        ];

        $builder
            ->add('mpCountry', EntityType::class, $mpCountryOptions)
            ->add('group', EntityType::class, $groupOptions)
            ->add('mapType', ChoiceType::class, [
                'label' => 'form.filter.matrix.matrix_type.label',
                'choices' => [
                    'form.filter.matrix.matrix_type.political_group' => 1,
                    'form.filter.matrix.matrix_type.country' => 2
                ],
                'expanded' => true,
                'choice_attr' => [
                    'form.filter.matrix.matrix_type.political_group' => ['data-target' => 'data-group'],
                    'form.filter.matrix.matrix_type.country' => ['data-target' => 'data-mpcountry'],
                ],
            ])
            ->add('country', EntityType::class, [
                'label' => 'form.filter.matrix.vote_related_to_country.label',
                'class' => Country::class,
                'placeholder' => 'form.filter.matrix.vote_related_to_country.placeholder',
                'choice_label' => 'label',
                'required' => false,
                'help' => 'form.filter.matrix.vote_related_to_country.help',
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er
                        ->createQueryBuilder('country')
                        ->innerJoin('country.relatedVotes', 'votes')
                        ->orderBy('country.label', 'ASC')
                    ;
                }
            ])
        ;

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (PreSubmitEvent $event) use ($groupOptions, $mpCountryOptions): void {
                $data = $event->getData();

                if (1 === (int) $data['mapType'] && '' === $data['group']) {
                    $event
                        ->getForm()
                        ->add('group', EntityType::class, array_merge($groupOptions, [
                            'constraints' => [new NotBlank()],
                        ]))
                    ;
                } elseif (2 === (int) $data['mapType'] && '' === $data['mpCountry']) {
                    $event
                        ->getForm()
                        ->add('mpCountry', EntityType::class, array_merge($mpCountryOptions, [
                            'constraints' => [new NotBlank()],
                        ]))
                    ;
                }
            }
        );
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
