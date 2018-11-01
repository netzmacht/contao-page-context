<?php

/**
 * Contao Page Context
 *
 * @package    contao-page-context
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2018 netzmacht David Molineus.
 * @license    LGPL-3.0 https://github.com/netzmacht/contao-page-context/blob/master/LICENSE
 * @filesource
 */

declare(strict_types=1);

namespace spec\Netzmacht\Contao\PageContext\Request;

use Contao\PageModel;
use Netzmacht\Contao\PageContext\Request\PageContext;
use PhpSpec\ObjectBehavior;

final class PageContextSpec extends ObjectBehavior
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
