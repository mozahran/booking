<?php

declare(strict_types=1);

namespace App\Security;

use App\Contract\Resolver\WorkspaceResolverInterface;
use App\Contract\Service\NexusInterface;
use App\Domain\Exception\WorkspaceNotFoundException;
use App\Entity\UserEntity;
use App\Request\SpaceRequest;
use App\Request\WorkspaceRequest;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class WorkspaceVoter extends Voter
{
    public const MANAGE = 'MANAGE_WORKSPACE';

    public function __construct(
        private readonly NexusInterface $nexus,
        private readonly WorkspaceResolverInterface $workspaceResolver,
    ) {
    }

    protected function supports(
        string $attribute,
        mixed $subject,
    ): bool {
        return self::MANAGE === $attribute;
    }

    /**
     * @param WorkspaceRequest|SpaceRequest $subject
     *
     * @throws WorkspaceNotFoundException
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

        $workspaceId = $subject->getWorkspaceId();
        $workspace = $this->workspaceResolver->resolve(id: $workspaceId);
        if ($this->nexus->isWorkspaceOwner(workspace: $workspace, user: $user)) {
            return true;
        }

        return false;
    }
}
