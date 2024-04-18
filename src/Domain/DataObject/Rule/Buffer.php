<?php

declare(strict_types=1);

namespace App\Domain\DataObject\Rule;

use App\Contract\DataObject\Denormalizable;
use App\Contract\DataObject\Normalizable;
use App\Contract\DataObject\RuleInterface;
use App\Domain\Enum\RuleType;

final readonly class Buffer implements Normalizable, Denormalizable, RuleInterface
{
    private function __construct(
        private int $value,
        private ?array $spaceIds = null,
    ) {
    }

    public function getType(): RuleType
    {
        return RuleType::BUFFER;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getSpaceIds(): ?array
    {
        return $this->spaceIds;
    }

    public function normalize(): array
    {
        return [
            'value' => $this->getValue(),
            'spaceIds' => $this->getSpaceIds(),
        ];
    }

    public static function denormalize(array $data): self
    {
        return new self(
            value: $data['value'],
            spaceIds: $data['spaceIds'],
        );
    }
}
