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
