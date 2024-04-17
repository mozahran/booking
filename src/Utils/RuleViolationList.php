<?php

declare(strict_types=1);

namespace App\Utils;

use App\Domain\Exception\RuleViolationException;

final class RuleViolationList
{
    /**
     * @param RuleViolationException[] $violations
     */
    public function __construct(
        private array $violations,
    ) {
    }

    public static function create(): self
    {
        return new self(violations: []);
    }

    public function isEmpty(): bool
    {
        return count($this->violations) === 0;
    }

    public function add(RuleViolationException $violation): self
    {
        $this->violations[] = $violation;

        return $this;
    }

    public function merge(array $violations): self
    {
        foreach ($violations as $violation) {
            $this->add($violation);
        }

        return $this;
    }

    /**
     * @return RuleViolationException[]
     */
    public function all(): array
    {
        return $this->violations;
    }

    public function asSingleException(): RuleViolationException
    {
        $messages = [];
        foreach ($this->violations as $violation) {
            $messages[] = $violation->getMessage();
        }

        return new RuleViolationException(message: implode(PHP_EOL, $messages));
    }
}
