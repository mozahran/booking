<?php

namespace App\Tests\Unit\Service;

use App\Builder\BookingBuilder;
use App\Contract\Repository\ProviderUserDataRepositoryInterface;
use App\Contract\Resolver\ProviderResolverInterface;
use App\Contract\Resolver\WorkspaceResolverInterface;
use App\Domain\DataObject\Booking\TimeRange;
use App\Domain\DataObject\Provider;
use App\Domain\DataObject\ProviderUserData;
use App\Domain\DataObject\Set\ProviderSet;
use App\Domain\DataObject\Set\ProviderUserDataSet;
use App\Domain\DataObject\Set\SpaceSet;
use App\Domain\DataObject\Set\WorkspaceSet;
use App\Domain\DataObject\Space;
use App\Domain\DataObject\Workspace;
use App\Domain\Enum\UserRole;
use App\Domain\Exception\WorkspaceNotFoundException;
use App\Entity\UserEntity;
use App\Service\Nexus;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class NexusTest extends TestCase
{
    public function testIsAdmin()
    {
        $nexus = new Nexus(
            workspaceResolver: $this->createMock(WorkspaceResolverInterface::class),
            providerResolver: $this->createMock(ProviderResolverInterface::class),
            providerUserDataRepository: $this->createMock(ProviderUserDataRepositoryInterface::class),
        );

        $user1Mock = $this->createMock(UserInterface::class);
        $user1Mock->method('getRoles')->willReturn([UserRole::ADMIN->value]);

        $user2Mock = $this->createMock(UserInterface::class);
        $user2Mock->method('getRoles')->willReturn([]);

        $this->assertTrue($nexus->isAdmin($user1Mock));
        $this->assertFalse($nexus->isAdmin($user2Mock));
    }

    /**
     * @dataProvider dataProviderForTestIsWorkspaceOwner
     */
    public function testIsWorkspaceOwner(
        bool $success,
        array $params,
    ): void {
        $workspace = $this->createTestWorkspace($params['providerId']);
        $providerResolverMock = $this->createMock(ProviderResolverInterface::class);
        $providerResolverMock
            ->method('resolveManyByUser')
            ->willReturn($params['providerSet']);

        $nexus = new Nexus(
            workspaceResolver: $this->createMock(WorkspaceResolverInterface::class),
            providerResolver: $providerResolverMock,
            providerUserDataRepository: $this->createMock(ProviderUserDataRepositoryInterface::class),
        );

        $actual = $nexus->isWorkspaceOwner(
            workspace: $workspace,
            user: $this->createTestUser(),
        );

        if (true === $success) {
            $this->assertTrue($actual);
        } else {
            $this->assertFalse($actual);
        }
    }

    /**
     * @dataProvider dataProviderForTestIsLinkedToProvider
     */
    public function testIsLinkedToProvider(
        bool $success,
        array $params,
    ): void {
        $nexus = new Nexus(
            workspaceResolver: $this->createMock(WorkspaceResolverInterface::class),
            providerResolver: $this->createMock(ProviderResolverInterface::class),
            providerUserDataRepository: $this->createProviderUserDataRepositoryMock($params['providerUserData']),
        );
        $provider = new Provider(
            name: 'Test Provider',
            active: 1,
            userId: 1,
            id: $params['providerId'],
        );
        $actual = $nexus->isLinkedToProvider(
            provider: $provider,
            user: $this->createTestUser(),
        );
        if (true === $success) {
            $this->assertTrue($actual);
        } else {
            $this->assertFalse($actual);
        }
    }

    /**
     * @dataProvider dataProviderForTestIsSpaceOwner
     */
    public function testIsSpaceOwner(
        bool $success,
        array $params,
    ): void {
        $workspaceResolverMock = $this->createMock(WorkspaceResolverInterface::class);
        $workspaceResolverMock
            ->method('resolve')
            ->willReturn(
                $this->createTestWorkspace($params['providerId']),
            );

        $nexus = new Nexus(
            workspaceResolver: $workspaceResolverMock,
            providerResolver: $this->createMock(ProviderResolverInterface::class),
            providerUserDataRepository: $this->createProviderUserDataRepositoryMock($params['providerUserData']),
        );
        $space = $this->createTestSpace();
        $user = $this->createTestUser();
        $actual = $nexus->isSpaceOwner(
            space: $space,
            user: $user,
        );
        if (true === $success) {
            $this->assertTrue($actual);
        } else {
            $this->assertFalse($actual);
        }
    }

    public function testSpaceOwnerWithNonExistingWorkspace()
    {
        $workspaceResolverMock = $this->createMock(WorkspaceResolverInterface::class);
        $workspaceResolverMock
            ->method('resolve')
            ->willThrowException(new WorkspaceNotFoundException(1));

        $nexus = new Nexus(
            workspaceResolver: $workspaceResolverMock,
            providerResolver: $this->createMock(ProviderResolverInterface::class),
            providerUserDataRepository: $this->createMock(ProviderUserDataRepositoryInterface::class),
        );

        $actual = $nexus->isSpaceOwner(
            space: $this->createTestSpace(),
            user: $this->createTestUser(),
        );

        $this->assertSame(false, $actual);
    }

    public function testIsBookingOwner(): void
    {
        $nexus = new Nexus(
            workspaceResolver: $this->createMock(WorkspaceResolverInterface::class),
            providerResolver: $this->createMock(ProviderResolverInterface::class),
            providerUserDataRepository: $this->createMock(ProviderUserDataRepositoryInterface::class),
        );

        $booking = (new BookingBuilder())
            ->setUserId(1)
            ->setSpaceId(1)
            ->setTimeRange(new TimeRange('2000-01-01', '2000-01-01'))
            ->build();

        $actual = $nexus->isBookingOwner(
            booking: $booking,
            user: $this->createTestUser(1),
        );

        $this->assertTrue($actual);

        $actual = $nexus->isBookingOwner(
            booking: $booking,
            user: $this->createTestUser(2),
        );
        $this->assertFalse($actual);
    }

    /**
     * @dataProvider dataProviderForTestIsOwnerOfSpaceSet
     */
    public function testIsOwnerOfSpaceSet(
        bool $success,
        array $params,
    ): void {
        $workspaceSet = new WorkspaceSet();
        foreach ($params['providerIds'] as $providerId) {
            $workspaceSet->add(
                new Workspace(
                    name: 'Test Provider',
                    active: true,
                    providerId: $providerId,
                    id: 1,
                ),
            );
        }
        $workspaceResolverMock = $this->createMock(WorkspaceResolverInterface::class);
        $workspaceResolverMock
            ->method('resolveMany')
            ->willReturn($workspaceSet);

        $nexus = new Nexus(
            workspaceResolver: $workspaceResolverMock,
            providerResolver: $this->createMock(ProviderResolverInterface::class),
            providerUserDataRepository: $this->createProviderUserDataRepositoryMock($params['providerUserData']),
        );

        $actual = $nexus->isOwnerOfSpaceSet(
            spaceSet: new SpaceSet([$this->createTestSpace()]),
            user: $this->createTestUser(),
        );
        if (true === $success) {
            $this->assertTrue($actual);
        } else {
            $this->assertFalse($actual);
        }
    }

    public static function dataProviderForTestIsWorkspaceOwner(): Generator
    {
        yield 'Case #1' => [
            'success' => true,
            'params' => [
                'providerId' => 1,
                'providerSet' => new ProviderSet([
                    new Provider(
                        name: 'Test Provider',
                        active: true,
                        userId: 1,
                        id: 1,
                    ),
                ]),
            ],
        ];

        yield 'Case #2' => [
            'success' => false,
            'params' => [
                'providerId' => 2,
                'providerSet' => new ProviderSet([
                    new Provider(
                        name: 'Test Provider',
                        active: true,
                        userId: 1,
                        id: 1,
                    ),
                ]),
            ],
        ];
    }

    public static function dataProviderForTestIsSpaceOwner(): Generator
    {
        yield 'Case #1' => [
            'success' => true,
            'params' => [
                'providerId' => 1,
                'providerUserData' => new ProviderUserDataSet([
                    new ProviderUserData(
                        providerId: 1,
                        userId: 1,
                        role: UserRole::OWNER->value,
                        active: true,
                    ),
                ]),
            ],
        ];

        yield 'Case #2' => [
            'success' => false,
            'params' => [
                'providerId' => 1,
                'providerUserData' => new ProviderUserDataSet([
                    new ProviderUserData(
                        providerId: 1,
                        userId: 1,
                        role: UserRole::USER->value,
                        active: true,
                    ),
                ]),
            ],
        ];
    }

    public static function dataProviderForTestIsLinkedToProvider(): Generator
    {
        yield 'Case #1' => [
            'success' => true,
            'params' => [
                'providerId' => 1,
                'providerUserData' => new ProviderUserDataSet([
                    new ProviderUserData(
                        providerId: 1,
                        userId: 1,
                        role: UserRole::OWNER->value,
                        active: true,
                    ),
                ]),
            ],
        ];

        yield 'Case #2' => [
            'success' => false,
            'params' => [
                'providerId' => 2,
                'providerUserData' => new ProviderUserDataSet([
                    new ProviderUserData(
                        providerId: 1,
                        userId: 1,
                        role: UserRole::OWNER->value,
                        active: true,
                    ),
                ]),
            ],
        ];

        yield 'Case #3' => [
            'success' => true,
            'params' => [
                'providerId' => 2,
                'providerUserData' => new ProviderUserDataSet([
                    new ProviderUserData(
                        providerId: 1,
                        userId: 1,
                        role: UserRole::OWNER->value,
                        active: true,
                    ),
                    new ProviderUserData(
                        providerId: 2,
                        userId: 1,
                        role: UserRole::OWNER->value,
                        active: true,
                    ),
                ]),
            ],
        ];
    }

    public static function dataProviderForTestIsOwnerOfSpaceSet(): Generator
    {
        yield 'Case #1' => [
            'success' => true,
            'params' => [
                'providerIds' => [1,2],
                'providerUserData' => new ProviderUserDataSet([
                    new ProviderUserData(
                        providerId: 1,
                        userId: 1,
                        role: UserRole::OWNER->value,
                        active: true,
                    ),
                    new ProviderUserData(
                        providerId: 2,
                        userId: 1,
                        role: UserRole::OWNER->value,
                        active: true,
                    ),
                ]),
            ],
        ];

        yield 'Case #2' => [
            'success' => false,
            'params' => [
                'providerIds' => [3],
                'providerUserData' => new ProviderUserDataSet([
                    new ProviderUserData(
                        providerId: 1,
                        userId: 1,
                        role: UserRole::OWNER->value,
                        active: true,
                    ),
                    new ProviderUserData(
                        providerId: 2,
                        userId: 1,
                        role: UserRole::OWNER->value,
                        active: true,
                    ),
                ]),
            ],
        ];
    }

    private function createProviderUserDataRepositoryMock(
        ProviderUserDataSet $providerUserDataSet,
    ): ProviderUserDataRepositoryInterface {
        $providerUserDataRepository = $this->createMock(ProviderUserDataRepositoryInterface::class);
        $providerUserDataRepository
            ->method('findManyByUser')
            ->willReturn($providerUserDataSet);

        return $providerUserDataRepository;
    }

    private function createTestUser(int $id = 1): UserEntity
    {
        $userMock = $this->createMock(UserEntity::class);
        $userMock->method('getId')->willReturn($id);

        return $userMock;
    }

    private function createTestWorkspace(int $providerId): Workspace
    {
        return new Workspace(
            name: 'Test Workspace',
            active: true,
            providerId: $providerId,
        );
    }

    /**
     * @return Space
     */
    private function createTestSpace(): Space
    {
        return new Space(
            name: 'Test Space',
            active: 1,
            workspaceId: 1,
            id: 1,
        );
    }
}
