<?php

declare(strict_types=1);

namespace spec\Netzmacht\Contao\PageContext\Exception;

use Netzmacht\Contao\PageContext\Exception\InitializePageContextFailed;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InitializePageContextFailedSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(InitializePageContextFailed::class);
    }

    public function it_creates_instance_for_determinate_page_id_failed(): void
    {
        $this->beConstructedThrough('determinatePageIdFailed');
        $this->shouldHaveType(InitializePageContextFailed::class);
    }

    public function it_creates_instance_for_invalid_page_id(): void
    {
        $this->beConstructedThrough('invalidPageId', [5]);
        $this->shouldHaveType(InitializePageContextFailed::class);
    }

    public function it_creates_instance_for_non_root_page(): void
    {
        $this->beConstructedThrough('noRootPage', [5]);
        $this->shouldHaveType(InitializePageContextFailed::class);
    }
}
