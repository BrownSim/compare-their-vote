<?php

namespace App\Form\Type;

use App\Entity\Member;
use App\Entity\MemberVote;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class MemberSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mainMember', EntityType::class, [
                'label' => 'form.filter.member_comparison.main_member.label',
                'class' => Member::class,
                'placeholder' => 'form.filter.member_comparison.main_member.placeholder',
                'choice_label' => function (?Member $member): string {
                    return $member ? $member->getFirstName() . ' ' . $member->getLastName() : '';
                },
            ])
            ->add('member', EntityType::class, [
                'label' => 'form.filter.member_comparison.compared_member.label',
                'class' => Member::class,
                'placeholder' => 'form.filter.member_comparison.main_member.placeholder',
                'choice_label' => function (?Member $member): string {
                    return $member ? $member->getFirstName() . ' ' . $member->getLastName() : '';
                },
            ])
            ->add('members', CollectionType::class, [
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => Member::class,
                    'choice_label' => function (?Member $member): string {
                        return $member ? $member->getFirstName() . ' ' . $member->getLastName() : '';
                    },
                    'placeholder' => 'form.filter.member_comparison.member_collection.placeholder'
                ],
                'constraints' => [
                    new Count(max: 3),
                ]
            ])
            ->add('voteValue', ChoiceType::class, [
                'label' => 'form.filter.member_comparison.vote_value',
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                     'global.vote_value.for' => MemberVote::VOTE_FOR,
                     'global.vote_value.against' => MemberVote::VOTE_AGAINST,
                     'global.vote_value.abstention' => MemberVote::VOTE_ABSTENTION,
                     'global.vote_value.not_vote' => MemberVote::VOTE_DID_NOT_VOTE,
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
