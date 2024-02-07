<?php

declare(strict_types=1);

namespace spec\Netzmacht\Contao\PageContext\Security;

use Contao\Model;
use Contao\PageModel;
use Netzmacht\Contao\PageContext\Request\PageContext;
use Netzmacht\Contao\PageContext\Security\PageContextVoter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ReflectionClass;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface as AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class PageContextVoterSpec extends ObjectBehavior
{
    private PageContext $pageContext;

    public function let(
        AuthenticationTrustResolver $trustResolver,
        AuthorizationChecker $authorizationChecker,
    ): void {
        $this->beConstructedWith($trustResolver, $authorizationChecker);

        $modelReflection = (new ReflectionClass(Model::class));
        if ($modelReflection->hasProperty('arrColumnCastTypes')) {
            $modelReflection->getProperty('arrColumnCastTypes')->setValue(['arrColumnCastTypes' => []]);
        }

        $pageReflection = new ReflectionClass(PageModel::class);
        $page           = $pageReflection->newInstanceWithoutConstructor();
        $rootPage       = (new ReflectionClass(PageModel::class))->newInstanceWithoutConstructor();

        $pageReflection->getProperty('blnDetailsLoaded')->setValue($page, true);
        $pageReflection->getProperty('blnDetailsLoaded')->setValue($rootPage, true);

        $this->pageContext = new PageContext($page, $rootPage);
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
        AuthenticationTrustResolver $trustResolver,
    ): void {
        $page     = (new ReflectionClass(PageModel::class))->newInstanceWithoutConstructor();
        $rootPage = (new ReflectionClass(PageModel::class))->newInstanceWithoutConstructor();

        $page->protected = true;
        $pageContext     = new PageContext($page, $rootPage);

        $trustResolver->isAuthenticated(Argument::any())->willReturn(false);

        $this->vote($token, $pageContext, [PageContextVoter::VIEW])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }
}
