<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Enum;

enum MatchStatus: string
{
    case VALID = 'VALID';
    case INVALID = 'INVALID';
    case NOT_PROCESSED = 'NOT_PROCESSED';
}
