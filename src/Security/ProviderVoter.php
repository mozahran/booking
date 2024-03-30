<?php

declare(strict_types=1);

namespace App\Security;

use App\Contract\Resolver\ProviderResolverInterface;
use App\Contract\Service\NexusInterface;
use App\Domain\Exception\ProviderNotFoundException;
use App\Entity\UserEntity;
use App\Request\WorkspaceRequest;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProviderVoter extends Voter
{
    public const MANAGE = 'MANAGE_PROVIDER';

    public function __construct(
        private readonly NexusInterface $nexus,
        private readonly ProviderResolverInterface $providerResolver,
    ) {
    }

    protected function supports(
        string $attribute,
        mixed $subject,
    ): bool {
        return self::MANAGE === $attribute;
    }

    /**
     * @throws ProviderNotFoundException
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
    ): bool {
        $user = $token->getUser();
        if (!$user instanceof UserEntity) {
            return false;
        }

        if (!$subject instanceof WorkspaceRequest) {
            return false;
        }

        if ($this->nexus->isAdmin(user: $user)) {
            return true;
        }

        $providerId = $subject->getProviderId();
        $provider = $this->providerResolver->resolve(id: $providerId);
        if ($this->nexus->isLinkedToProvider(provider: $provider, user: $user)) {
            return true;
        }

        return false;
    }
}
