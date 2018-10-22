<?php

declare(strict_types=1);

namespace spec\Netzmacht\Contao\PageContext\Request;

use Contao\PageModel;
use Netzmacht\Contao\PageContext\Request\PageContext;
use PhpSpec\ObjectBehavior;

class PageContextSpec extends ObjectBehavior
{
    public function let(PageModel $pageModel, PageModel $rootPage): void
    {
        $this->beConstructedWith($pageModel, $rootPage);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PageContext::class);
    }

    public function it_contains_page(): void
    {
        $this->page()->shouldBeAnInstanceOf(PageModel::class);
    }

    public function it_contains_root_page(): void
    {
        $this->rootPage()->shouldBeAnInstanceOf(PageModel::class);
    }
}
