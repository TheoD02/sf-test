<?php

namespace Module\ApiPlatformEasyFilter\Relation;

/**
 * @template T of object
 */
class Relation
{
    /**
     * @var array<string>
     */
    private array $set = [];

    /**
     * @var array<string>
     */
    private array $add = [];

    /**
     * @var array<string>
     */
    private array $remove = [];

    public function getSet(): array
    {
        return $this->set;
    }

    public function setSet(array $set): void
    {
        $this->set = $set;
    }

    public function getAdd(): array
    {
        return $this->add;
    }

    public function setAdd(array $add): void
    {
        $this->add = $add;
    }

    public function getRemove(): array
    {
        return $this->remove;
    }

    public function setRemove(array $remove): void
    {
        $this->remove = $remove;
    }
}
