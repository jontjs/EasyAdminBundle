<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Security;

use Symfony\Component\Security\Core\Authorization\AccessDecision;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * A slightly modified authorization checker optimized for performance and which
 * doesn't trigger exceptions when security is not enabled.
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class AuthorizationChecker implements AuthorizationCheckerInterface
{
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function isGranted(mixed $attribute, mixed $subject = null, ?AccessDecision $accessDecision = null): bool
    {
        // this check is needed for performance reasons because most of the time permissions
        // won't be set, so this function must return as early as possible in those cases
        if (null === $attribute || '' === $attribute) {
            return true;
        }

        try {
            return $this->authorizationChecker->isGranted($attribute, $subject);
        } catch (AuthenticationCredentialsNotFoundException) {
            // this exception happens when there's no security configured in the application
            // that's a valid scenario for EasyAdmin, where security is not required (although very common)
            return true;
        }
    }
}
