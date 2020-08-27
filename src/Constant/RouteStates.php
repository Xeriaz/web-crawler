<?php

declare(strict_types=1);

/**
 * @copyright C UAB NFQ Technologies
 * This Software is the property of NFQ Technologies
 * and is protected by copyright law – it is NOT Freeware.
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * Contact UAB NFQ Technologies:
 * E-mail: info@nfq.lt
 * http://www.nfq.lt
 */

namespace App\Constant;

class RouteStates
{
    public const PENDING = 'pending';
    public const IN_PROGRESS = 'in_progress';
    public const SUCCESS = 'success';
    public const FAILED = 'failed';
    public const DEAD = 'dead';
    public const OUTER = 'outer';
}
