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

namespace spec\Netzmacht\Contao\PageContext\Security;

use Contao\PageModel;
use Netzmacht\Contao\PageContext\Request\PageContext;
use Netzmacht\Contao\PageContext\Security\PageContextVoter;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface as AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class PageContextVoterSpec extends ObjectBehavior
{
    /**
     * @var PageContext
     */
    private $pageContext;

    public function let(
        AuthenticationTrustResolver $trustResolver,
        AuthorizationChecker $authorizationChecker,
        PageModel $page,
        PageModel $rootPage
    ): void {
        $this->beConstructedWith($trustResolver, $authorizationChecker);

        $this->pageContext = new PageContext($page->getWrappedObject(), $rootPage->getWrappedObject());
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PageContextVoter::class);
    }

    public function it_abstain_access_to_unknown_subject(TokenInterface $token): void
    {
        $this->vote($token, 'FOO', [PageContextVoter::VIEW])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    public function it_abstain_access_to_unprotected_page_context_with_no_view_attribute(TokenInterface $token): void
    {
        $this->vote($token, $this->pageContext, ['edit'])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    public function it_grants_access_to_unprotected_page_context_with_view_attribute(TokenInterface $token): void
    {
        $this->vote($token, $this->pageContext, [PageContextVoter::VIEW])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    public function it_denies_access_to_unprotected_page_context_with_view_attribute(
        TokenInterface $token,
        PageModel $page,
        PageModel $rootPage
    ): void {
        $page->protected = true;
        $pageContext     = new PageContext($page->getWrappedObject(), $rootPage->getWrappedObject());

        $token->isAuthenticated()->willReturn(false);

        $this->vote($token, $pageContext, [PageContextVoter::VIEW])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }
}
