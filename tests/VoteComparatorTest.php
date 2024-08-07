<?php

namespace App\Tests;

use App\Entity\Member;
use App\Entity\MemberVote;
use App\Entity\Vote;
use App\Manager\MemberManager;
use App\Repository\MemberVoteRepository;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class VoteComparatorTest extends TestCase
{
    private array $data = [];

    protected function setUp(): void
    {
        $this->data = $this->generateComparison();
    }

    public function testTotalVote(): void
    {
        $this->assertEquals(2, $this->data[0]['data']['total']);
    }

    public function testRate(): void
    {
        $this->assertEquals(50.0, $this->data[0]['data']['rate']['same']);
        $this->assertEquals(50.0, $this->data[0]['data']['rate']['difference']);
    }

    public function testSameVote()
    {
        $this->assertEquals(1, $this->data[0]['data']['same']);
    }

    public function testDifferentVote()
    {
        $this->assertEquals(1, $this->data[0]['data']['difference']);
    }

    private function generateComparison(): array
    {
        $member1 = (new Member())
            ->setFirstName('Mocked')
            ->setLastName('Do')
        ;

        $member2 = (new Member())
            ->setFirstName('Mocked')
            ->setLastName('Smith')
        ;

        $memberVoteRepository = $this->createMock(MemberVoteRepository::class);
        $memberVoteRepository->expects($this->any())
            ->method('findFeaturedVotesByMember')
            ->willReturnOnConsecutiveCalls($this->generateFirstMemberVotes($member1), $this->generateOtherMemberVotes($member2))
        ;

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($memberVoteRepository)
        ;

        $memberManager = new MemberManager($entityManager);

        return $memberManager->compare($member1, [$member2], []);
    }

    private function generateFirstMemberVotes(Member $member): array
    {
        // comparison use entity id, entity don't have id setter
        // So we have to mock it
        $vote = $this->getMockBuilder(Vote::class)->getMock();
        $vote->method('getId')->willReturn(1);

        $memberVote1 = (new MemberVote())
            ->setVote($vote)
            ->setValue(MemberVote::VOTE_FOR)
            ->setMember($member)
        ;

        $vote = $this->getMockBuilder(Vote::class)->getMock();
        $vote->method('getId')->willReturn(2);

        $memberVote2 = (new MemberVote())
            ->setVote($vote)
            ->setValue(MemberVote::VOTE_AGAINST)
            ->setMember($member)
        ;

        return [$memberVote1, $memberVote2];
    }

    private function generateOtherMemberVotes(Member $member): array
    {
        // comparison use entity id, entity don't have id setter
        // So we have to mock it
        $vote = $this->getMockBuilder(Vote::class)->getMock();
        $vote->method('getId')->willReturn(1);

        $memberVote1 = (new MemberVote())
            ->setVote($vote)
            ->setValue(MemberVote::VOTE_FOR)
            ->setMember($member)
        ;

        $vote = $this->getMockBuilder(Vote::class)->getMock();
        $vote->method('getId')->willReturn(2);

        $memberVote2 = (new MemberVote())
            ->setVote($vote)
            ->setValue(MemberVote::VOTE_DID_NOT_VOTE)
            ->setMember($member)
        ;

        return [$memberVote1, $memberVote2];
    }
}
