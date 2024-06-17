<?php

namespace App\Form\Type;

use App\Entity\Member;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Count;

class MemberSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mainMember', EntityType::class, [
                'class' => Member::class,
                'choice_label' => 'lastName',
            ])
            ->add('member', EntityType::class, [
                'class' => Member::class,
                'choice_label' => 'lastName',
            ])
            ->add('members', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => ['class' => Member::class, 'choice_label' => 'lastName', 'placeholder' => 'Choisir un député'],
                'allow_add' => true,
                'allow_delete' => true,
                'constraints' => [
                    new Count(max: 3),
                ]
            ])
            ->add('voteValue', ChoiceType::class, [
                'label' => 'Valeur de vote',
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                     'vote_value.for' => 'FOR',
                     'vote_value.against' => 'AGAINST',
                     'vote_value.miss' => 'ABSTENTION',
                     'vote_value.not_vote' => 'DID_NOT_VOTE',
                ],
            ])
        ;
    }
}
