<?php

namespace App\Twig;

use App\Normaliser\TomSelect\MemberNormalizer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FunctionExtension extends AbstractExtension
{
    public function __construct(
        private readonly MemberNormalizer $memberNormalizer,
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('members_tom_select', [$this, 'memberTomSelect']),
        ];
    }

    public function memberTomSelect(): string
    {
        return $this->memberNormalizer->membersToTomSelect();
    }
}
