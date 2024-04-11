<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Denormalizable;
use App\Contract\DataObject\Normalizable;
use App\Contract\DataObject\RuleInterface;
use App\Domain\Enum\Rule\Predicate;
use App\Domain\Enum\RuleType;

final class Window implements Normalizable, Denormalizable, RuleInterface
{
    /**
     * @param string[]|null $roles
     * @param int[]|null    $spaceIds
     */
    public function __construct(
        private readonly Predicate $predicate,
        private readonly int $value,
        private readonly ?array $roles = null,
        private readonly ?array $spaceIds = null,
    ) {
    }

    public function getType(): RuleType
    {
        return RuleType::WINDOW;
    }

    public function getPredicate(): Predicate
    {
        return $this->predicate;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @return string[]|null
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    /**
     * @return int[]|null
     */
    public function getSpaceIds(): ?array
    {
        return $this->spaceIds;
    }

    public function normalize(): array
    {
        return [
            'predicate' => $this->getPredicate()->value,
            'value' => $this->getValue(),
            'roles' => $this->getRoles(),
            'spaceIds' => $this->getSpaceIds(),
        ];
    }

    public static function denormalize(array $data): self
    {
        return new self(
            predicate: Predicate::from($data['predicate']),
            value: $data['value'],
            roles: $data['roles'],
            spaceIds: $data['spaceIds'],
        );
    }
}
