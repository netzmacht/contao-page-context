<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Security;

use Contao\FrontendUser;
use Netzmacht\Contao\PageContext\Request\PageContext;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface as AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as Token;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use function count;
use function is_array;

/**
 * Class PageContextVoter
 */
final class PageContextVoter extends Voter
{
    public const VIEW = 'view';

    /**
     * Authentication trust resolver.
     *
     * @var AuthenticationTrustResolver
     */
    private $trustResolver;

    /**
     * Authorization checker.
     *
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * PageContextVoter constructor.
     *
     * @param AuthenticationTrustResolver $trustResolver        Authentication trust resolver.
     * @param AuthorizationChecker        $authorizationChecker Authorization checker.
     */
    public function __construct(
        AuthenticationTrustResolver $trustResolver,
        AuthorizationChecker $authorizationChecker
    ) {
        $this->trustResolver        = $trustResolver;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject): bool
    {
        if ($attribute !== self::VIEW) {
            return false;
        }

        if ($subject instanceof PageContext) {
            return true;
        }

        return false;
    }

    /**
     * Vote the page context.
     *
     * @param string      $attribute   The attribute.
     * @param PageContext $pageContext The page context.
     * @param Token       $token       The authentication token.
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $pageContext, Token $token): bool
    {
        $page = $pageContext->page();

        if (!$page->protected) {
            return true;
        }

        if ($this->trustResolver->isAnonymous($token)) {
            return false;
        }

        if (!$this->authorizationChecker->isGranted('ROLE_MEMBER')) {
            return false;
        }

        $user = $token->getUser();
        if (!$user instanceof FrontendUser) {
            return false;
        }

        $groups = $page->groups;
        if (empty($groups) || !is_array($groups) || !count(array_intersect($groups, $user->groups))) {
            return false;
        }

        return true;
    }
}
