<?php

declare(strict_types=1);

namespace App\Constant;

interface LinksStates
{
    public const PENDING = 'pending';
    public const SUCCESS = 'success';
    public const FAILED = 'failed';
    public const SKIPPED = 'skipped';
    public const DEAD = 'dead';
}
