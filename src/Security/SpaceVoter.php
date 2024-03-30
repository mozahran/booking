<?php

declare(strict_types=1);

namespace App\Security;

use App\Contract\Resolver\SpaceResolverInterface;
use App\Contract\Service\NexusInterface;
use App\Domain\Exception\SpaceNotFoundException;
use App\Entity\UserEntity;
use App\Request\SpaceRequest;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SpaceVoter extends Voter
{
    public const MANAGE = 'MANAGE_SPACE';

    public function __construct(
        private readonly NexusInterface $nexus,
        private readonly SpaceResolverInterface $spaceResolver,
    ) {
    }

    protected function supports(
        string $attribute,
        mixed $subject,
    ): bool {
        return self::MANAGE === $attribute;
    }

    /**
     * @param SpaceRequest $subject
     *
     * @throws SpaceNotFoundException
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

        if ($this->nexus->isAdmin(user: $user)) {
            return true;
        }

        $spaceId = $subject->getSpaceId();
        $space = $this->spaceResolver->resolve(id: $spaceId);
        if ($this->nexus->isSpaceOwner(space: $space, user: $user)) {
            return true;
        }

        return false;
    }
}
