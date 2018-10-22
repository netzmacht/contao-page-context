<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Request;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\NoLayoutSpecifiedException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Image\PictureFactoryInterface;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\ThemeModel;
use Netzmacht\Contao\Toolkit\Callback\Invoker;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

final class PageContextInitializer
{
    private $defaults;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var Invoker
     */
    private $callbackInvoker;

    /**
     * @var PictureFactoryInterface
     */
    private $pictureFactory;

    /**
     * @var RepositoryManager
     */
    private $repositoryManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function initialize(PageContext $context, Request $request)
    {
        $this->initializeUserLoggedInConstants();
        $this->initializeGlobals($context);
        $this->initializeLocale($context, $request);
        $this->initializeStaticUrls();
        $this->initializePageLayout($context);
    }

    private function initializeUserLoggedInConstants(): void
    {
        if (!defined('BE_USER_LOGGED_IN')) {
            define('BE_USER_LOGGED_IN', $this->defaults['BE_USER_LOGGED_IN']);
        }

        if (!defined('FE_USER_LOGGED_IN')) {
            define('FE_USER_LOGGED_IN', $this->defaults['BE_USER_LOGGED_IN']);
        }
    }

    private function initializeGlobals(PageContext $context): void
    {
        $page = $context->page();

        if ($page->adminEmail != '') {
            $adminEmail = $page->adminEmail;
        } else {
            $adminEmail = $this->framework->getAdapter(Config::class)->get('adminEmail');
        }

        [$GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']] = StringUtil::splitFriendlyEmail($adminEmail);

        $GLOBALS['objPage']     = $page;
        $GLOBALS['TL_KEYWORDS'] = '';
        $GLOBALS['TL_LANGUAGE'] = $page->language;
    }

    private function initializeLocale(PageContext $context, Request $request): void
    {
        $locale = str_replace('-', '_', $context->page()->language);

        $request->setLocale($locale);
        $this->translator->setLocale($locale);

        $this->framework->getAdapter(System::class)->loadLanguageFile('default');
    }

    private function initializeStaticUrls(): void
    {
        $this->framework->getAdapter(Controller::class)->setStaticUrls();
    }

    private function initializePageLayout(PageContext $context, Request $request): void
    {
        $page   = $context->page();
        $layout = $this->getPageLayout($page, $request);

        if (isset($GLOBALS['TL_HOOKS']['getPageLayout']) && \is_array($GLOBALS['TL_HOOKS']['getPageLayout'])) {
            $this->callbackInvoker->invokeAll($GLOBALS['TL_HOOKS']['getPageLayout'], [$page, $layout, $this]);
        }

        /** @var ThemeModel $objTheme */
        $objTheme = $layout->getRelated('pid');

        // Set the default image densities
        $this->pictureFactory->setDefaultDensities($objTheme->defaultImageDensities);

        // Store the layout ID
        $page->layoutId = $layout->id;

        // Set the layout template and template group
        $page->template = $layout->template ?: 'fe_page';
        $page->templateGroup = $objTheme->templates;

        // Store the output format
        [$strFormat, $strVariant] = explode('_', $layout->doctype);

        $page->outputFormat = $strFormat;
        $page->outputVariant = $strVariant;
    }

    /**
     * Get a page layout and return it as database result object
     *
     * @param PageModel $pageModel
     * @param Request   $request
     *
     * @return LayoutModel
     */
    private function getPageLayout(PageModel $pageModel, Request $request): LayoutModel
    {
        /** @var LayoutModel $layoutModel */
        $isMobile    = $request->query->get('view') === 'mobile';
        $layoutId    = (int) (($isMobile && $pageModel->mobileLayout) ? $pageModel->mobileLayout : $pageModel->layout);
        $layoutModel = $this->repositoryManager->getRepository(LayoutModel::class)->find($layoutId);

        // Die if there is no layout
        if ($layoutModel === null) {
            $this->logger->log(
                LogLevel::ERROR,
                'Could not find layout ID "' . $layoutId . '"',
                ['contao' => new ContaoContext(__METHOD__, LogLevel::ERROR)]
            );
            throw new NoLayoutSpecifiedException('No layout specified');
        }

        $pageModel->hasJQuery   = $layoutModel->addJQuery;
        $pageModel->hasMooTools = $layoutModel->addMooTools;
        $pageModel->isMobile    = $isMobile;

        return $layoutModel;
    }
}
