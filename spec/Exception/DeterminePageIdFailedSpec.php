<?php

declare(strict_types=1);

namespace spec\Netzmacht\Contao\PageContext\Exception;

use Netzmacht\Contao\PageContext\Exception\DeterminePageIdFailed;
use PhpSpec\ObjectBehavior;
use RuntimeException;

class DeterminePageIdFailedSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DeterminePageIdFailed::class);
        $this->shouldHaveType(RuntimeException::class);
    }
}
