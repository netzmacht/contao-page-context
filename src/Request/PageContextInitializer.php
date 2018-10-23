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
use function define;
use function defined;

/**
 * Class PageContextInitializer initialize the page context which is usually done by the Contao regular page.
 */
final class PageContextInitializer
{
    /**
     * Default config.
     *
     * @var array
     */
    private $defaults = [
        'BE_USER_LOGGED_IN' => false,
        'FE_USER_LOGGED_IN' => false,
    ];

    /**
     * Translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Contao framework.
     *
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * Callback invoker.
     *
     * @var Invoker
     */
    private $callbackInvoker;

    /**
     * Picture factory.
     *
     * @var PictureFactoryInterface
     */
    private $pictureFactory;

    /**
     * Repository manager.
     *
     * @var RepositoryManager
     */
    private $repositoryManager;

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PageContextInitializer constructor.
     *
     * @param TranslatorInterface      $translator        Translator.
     * @param ContaoFrameworkInterface $framework         Contao framework.
     * @param Invoker                  $callbackInvoker   Callback invoker.
     * @param PictureFactoryInterface  $pictureFactory    Picture factory.
     * @param RepositoryManager        $repositoryManager Repository manager.
     * @param LoggerInterface          $logger            Logger.
     * @param array                    $defaults          Default config to override default configs.
     */
    public function __construct(
        TranslatorInterface $translator,
        ContaoFrameworkInterface $framework,
        Invoker $callbackInvoker,
        PictureFactoryInterface $pictureFactory,
        RepositoryManager $repositoryManager,
        LoggerInterface $logger,
        array $defaults = []
    ) {
        $this->translator        = $translator;
        $this->framework         = $framework;
        $this->callbackInvoker   = $callbackInvoker;
        $this->pictureFactory    = $pictureFactory;
        $this->repositoryManager = $repositoryManager;
        $this->logger            = $logger;
        $this->defaults          = array_merge($this->defaults, $defaults);
    }

    /**
     * Initialize the page context.
     *
     * @param PageContext $context Page context.
     * @param Request     $request Web request.
     *
     * @return void
     */
    public function initialize(PageContext $context, Request $request): void
    {
        $this->initializeUserLoggedInConstants();
        $this->initializeGlobals($context);
        $this->initializeLocale($context, $request);
        $this->initializeStaticUrls();
        $this->initializePageLayout($context, $request);
    }

    /**
     * Initialize user logged in constants set by default.
     *
     * You can't trust this constants, as only defaults values are set right now.
     *
     * @return void
     */
    private function initializeUserLoggedInConstants(): void
    {
        if (!defined('BE_USER_LOGGED_IN')) {
            define('BE_USER_LOGGED_IN', $this->defaults['BE_USER_LOGGED_IN']);
        }

        if (!defined('FE_USER_LOGGED_IN')) {
            define('FE_USER_LOGGED_IN', $this->defaults['FE_USER_LOGGED_IN']);
        }
    }

    /**
     * Initialize globals set by Contao.
     *
     * @param PageContext $context The page context.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
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

    /**
     * Initialize the locale.
     *
     * @param PageContext $context Page context.
     * @param Request     $request Web request.
     *
     * @return void
     */
    private function initializeLocale(PageContext $context, Request $request): void
    {
        $locale = str_replace('-', '_', $context->page()->language);

        $request->setLocale($locale);
        $this->translator->setLocale($locale);

        $this->framework->getAdapter(System::class)->loadLanguageFile('default');
    }

    /**
     * Initialize static urls.
     *
     * @return void
     */
    private function initializeStaticUrls(): void
    {
        $this->framework->getAdapter(Controller::class)->setStaticUrls();
    }

    /**
     * Initialize the page layout.
     *
     * @param PageContext $context Page context.
     * @param Request     $request Web request.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function initializePageLayout(PageContext $context, Request $request): void
    {
        $page   = $context->page();
        $layout = $this->getPageLayout($page, $request);

        if (isset($GLOBALS['TL_HOOKS']['getPageLayout']) && \is_array($GLOBALS['TL_HOOKS']['getPageLayout'])) {
            $this->callbackInvoker->invokeAll($GLOBALS['TL_HOOKS']['getPageLayout'], [$page, $layout, $this]);
        }

        /** @var ThemeModel $theme */
        $theme = $this->repositoryManager->getRepository(ThemeModel::class)->find((int) $layout->pid);

        // Set the default image densities
        $this->pictureFactory->setDefaultDensities($theme->defaultImageDensities);

        // Store the layout ID
        $page->layoutId = $layout->id;

        // Set the layout template and template group
        $page->template      = $layout->template ?: 'fe_page';
        $page->templateGroup = $theme->templates;

        // Store the output format
        [$strFormat, $strVariant] = explode('_', $layout->doctype);

        $page->outputFormat  = $strFormat;
        $page->outputVariant = $strVariant;
    }

    /**
     * Get a page layout and return it as database result object
     *
     * @param PageModel $pageModel The page model.
     * @param Request   $request   Web request.
     *
     * @return LayoutModel
     *
     * @throws NoLayoutSpecifiedException If no page layout could be found.
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
