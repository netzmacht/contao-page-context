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
use Contao\PageRegular;
use Contao\StringUtil;
use Contao\System;
use Contao\ThemeModel;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

use function array_merge;
use function assert;
use function define;
use function defined;
use function explode;
use function is_array;
use function is_string;
use function str_replace;

/**
 * Class PageContextInitializer initialize the page context which is usually done by the Contao regular page.
 */
final class ContaoPageContextInitializer implements PageContextInitializer
{
    /**
     * Default config.
     *
     * @var array<string,bool>
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
     * @param TranslatorInterface      $translator        Translator.
     * @param ContaoFrameworkInterface $framework         Contao framework.
     * @param PictureFactoryInterface  $pictureFactory    Picture factory.
     * @param RepositoryManager        $repositoryManager Repository manager.
     * @param LoggerInterface          $logger            Logger.
     * @param array<string,bool>       $defaults          Default config to override default configs.
     */
    public function __construct(
        TranslatorInterface $translator,
        ContaoFrameworkInterface $framework,
        PictureFactoryInterface $pictureFactory,
        RepositoryManager $repositoryManager,
        LoggerInterface $logger,
        array $defaults = []
    ) {
        $this->translator        = $translator;
        $this->framework         = $framework;
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
     */
    public function initialize(PageContext $context, Request $request): void
    {
        $this->framework->initialize();
        $this->initializeUserLoggedInConstants();
        $this->initializeGlobals($context);
        $this->initializeLocale($context, $request);
        $this->initializeStaticUrls();
        $this->initializePageLayout($context);
    }

    /**
     * Initialize user logged in constants set by default.
     *
     * You can't trust this constants, as only defaults values are set right now.
     */
    private function initializeUserLoggedInConstants(): void
    {
        if (! defined('BE_USER_LOGGED_IN')) {
            define('BE_USER_LOGGED_IN', $this->defaults['BE_USER_LOGGED_IN']);
        }

        if (defined('FE_USER_LOGGED_IN')) {
            return;
        }

        define('FE_USER_LOGGED_IN', $this->defaults['FE_USER_LOGGED_IN']);
    }

    /**
     * Initialize globals set by Contao.
     *
     * @param PageContext $context The page context.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function initializeGlobals(PageContext $context): void
    {
        $page = $context->page();

        if ($page->adminEmail !== '') {
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
     */
    private function initializeStaticUrls(): void
    {
        $this->framework->getAdapter(Controller::class)->setStaticUrls();
    }

    /**
     * Initialize the page layout.
     *
     * @param PageContext $context Page context.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function initializePageLayout(PageContext $context): void
    {
        $page        = $context->page();
        $layout      = $this->getPageLayout($page);
        $pageRegular = new PageRegular();

        if (isset($GLOBALS['TL_HOOKS']['getPageLayout']) && is_array($GLOBALS['TL_HOOKS']['getPageLayout'])) {
            $systemAdapter = $this->framework->getAdapter(System::class);
            foreach ($GLOBALS['TL_HOOKS']['generatePage'] as $callback) {
                $callback[0] = $systemAdapter->__call('importStatic', [$callback[0]]);
                $callback[0]->{$callback[1]}($page, $layout, $pageRegular);
            }
        }

        $theme = $this->repositoryManager->getRepository(ThemeModel::class)->find((int) $layout->pid);
        assert($theme instanceof ThemeModel);

        // Set the default image densities
        $this->pictureFactory->setDefaultDensities($theme->defaultImageDensities);

        // Store the layout ID
        $page->layoutId = $layout->id;

        // Set the layout template and template group
        $page->template      = $layout->template ?: 'fe_page';
        $page->templateGroup = $theme->templates;

        $doctype = $layout->doctype;
        if (! is_string($doctype)) {
            return;
        }

        // Store the output format
        [$strFormat, $strVariant] = explode('_', $doctype);

        $page->outputFormat  = $strFormat;
        $page->outputVariant = $strVariant;
    }

    /**
     * Get a page layout and return it as database result object
     *
     * @param PageModel $pageModel The page model.
     *
     * @throws NoLayoutSpecifiedException If no page layout could be found.
     */
    private function getPageLayout(PageModel $pageModel): LayoutModel
    {
        /** @var LayoutModel|null $layoutModel */
        $layoutId    = (int) $pageModel->layout;
        $layoutModel = $this->repositoryManager->getRepository(LayoutModel::class)->find($layoutId);

        // Die if there is no layout
        if (! $layoutModel instanceof LayoutModel) {
            $this->logger->log(
                LogLevel::ERROR,
                'Could not find layout ID "' . $layoutId . '"',
                ['contao' => new ContaoContext(__METHOD__, LogLevel::ERROR)]
            );

            throw new NoLayoutSpecifiedException('No layout specified');
        }

        $pageModel->hasJQuery   = $layoutModel->addJQuery;
        $pageModel->hasMooTools = $layoutModel->addMooTools;

        return $layoutModel;
    }
}
