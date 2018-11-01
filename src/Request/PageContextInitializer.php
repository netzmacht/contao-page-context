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

namespace Netzmacht\Contao\PageContext\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface PageContextInitializer
 */
interface PageContextInitializer
{
    /**
     * Initialize the page context.
     *
     * @param PageContext $context Page context.
     * @param Request     $request Web request.
     *
     * @return void
     */
    public function initialize(PageContext $context, Request $request): void;
}
