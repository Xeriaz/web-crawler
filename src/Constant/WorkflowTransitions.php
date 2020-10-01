<?php

declare(strict_types=1);

namespace App\Constant;

interface WorkflowTransitions
{
    public const SKIPPING = 'skipping';
    public const SUCCESS = 'success';
    public const PENDING = 'pending';
    public const REDIRECTING = 'redirecting';
    public const FAILING = 'failing';
    public const DYING = 'dying';
}
