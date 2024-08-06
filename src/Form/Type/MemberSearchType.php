<?php

namespace App\Form\Type;

use App\Entity\Member;
use App\Entity\MemberVote;
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
                'label' => 'main_member',
                'class' => Member::class,
                'placeholder' => 'choice_member',
                'choice_label' => function (?Member $member): string {
                    return $member ? $member->getFirstName() . ' ' . $member->getLastName() : '';
                },
            ])
            ->add('member', EntityType::class, [
                'label' => 'member',
                'class' => Member::class,
                'choice_label' => function (?Member $member): string {
                    return $member ? $member->getFirstName() . ' ' . $member->getLastName() : '';
                },
                'placeholder' => 'choice_member',
            ])
            ->add('members', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => Member::class,
                    'choice_label' => function (?Member $member): string {
                        return $member ? $member->getFirstName() . ' ' . $member->getLastName() : '';
                    },
                    'placeholder' => 'choice_member'
                ],
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'label_add' => 'add_member',
                'constraints' => [
                    new Count(max: 3),
                ]
            ])
            ->add('voteValue', ChoiceType::class, [
                'label' => 'vote_value_label',
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                     'vote_value.for' => MemberVote::VOTE_FOR,
                     'vote_value.against' => MemberVote::VOTE_AGAINST,
                     'vote_value.miss' => MemberVote::VOTE_ABSTENTION,
                     'vote_value.not_vote' => MemberVote::VOTE_DID_NOT_VOTE,
                ],
            ])
        ;
    }
}
