<?php

namespace NickMous\Binsta\Internals\Factories;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use InvalidArgumentException;

abstract class Factory
{
    protected static Generator $faker;
    protected int $count = 1;
    /** @var array<string, mixed> */
    protected array $overrides = [];
    /** @var array<string, mixed> */
    protected array $states = [];

    public function __construct()
    {
        if (!isset(self::$faker)) {
            self::$faker = FakerFactory::create();
        }
    }

    /** @return array<string, mixed> */
    abstract public function definition(): array;

    abstract public function modelClass(): string;

    /** @return static */
    public static function new(): static
    {
        return new static(); // @phpstan-ignore-line
    }

    public function count(int $count): static
    {
        $this->count = $count;
        return $this;
    }

    /** @param array<string, mixed> $attributes */
    public function state(array $attributes): static
    {
        $this->overrides = array_merge($this->overrides, $attributes);
        return $this;
    }

    /** @param array<string, mixed> $attributes
     * @return object|array<int, object>
     */
    public function create(array $attributes = []): object|array
    {
        $attributes = array_merge($this->overrides, $attributes);

        if ($this->count === 1) {
            return $this->makeOne($attributes);
        }

        $items = [];
        for ($i = 0; $i < $this->count; $i++) {
            $items[] = $this->makeOne($attributes);
        }

        return $items;
    }

    /** @param array<string, mixed> $attributes
     * @return object|array<int, object>
     */
    public function make(array $attributes = []): object|array
    {
        $attributes = array_merge($this->overrides, $attributes);

        if ($this->count === 1) {
            return $this->createInstance($attributes);
        }

        $items = [];
        for ($i = 0; $i < $this->count; $i++) {
            $items[] = $this->createInstance($attributes);
        }

        return $items;
    }

    /** @param array<string, mixed> $attributes */
    private function makeOne(array $attributes = []): object
    {
        $instance = $this->createInstance($attributes);
        $instance->save();
        return $instance;
    }

    /** @param array<string, mixed> $attributes */
    private function createInstance(array $attributes = []): object
    {
        $modelClass = $this->modelClass();

        if (!class_exists($modelClass)) {
            throw new InvalidArgumentException("Model class {$modelClass} does not exist");
        }

        $definition = array_merge($this->definition(), $attributes);
        $instance = new $modelClass();

        foreach ($definition as $property => $value) {
            if (property_exists($instance, $property)) {
                $instance->$property = $value;
            }
        }

        return $instance;
    }

    protected function faker(): Generator
    {
        return self::$faker;
    }
}
