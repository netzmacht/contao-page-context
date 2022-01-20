<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Request;

use Symfony\Component\HttpFoundation\Request;

interface PageContextInitializer
{
    /**
     * Initialize the page context.
     *
     * @param PageContext $context Page context.
     * @param Request     $request Web request.
     */
    public function initialize(PageContext $context, Request $request): void;
}
