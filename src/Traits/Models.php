<?php

declare(strict_types=1);

namespace Codin\DBAL\Traits;

use Codin\DBAL\Contracts;
use ReflectionClass;
use ReflectionProperty;

trait Models
{
    /**
     * Get public properties on a model that can be set. Returns key-value array.
     */
    protected function getModelProps(Contracts\Model $model): array
    {
        $definitions = [];
        $ref = new ReflectionClass($model);
        $props = $ref->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($props as $prop) {
            $definitions[$prop->getName()] = $prop->isInitialized($model) ? $model->{$prop->getName()} : null;
        }

        return $definitions;
    }

    /**
     * Create a new entity
     */
    protected function createModel(): Contracts\Model
    {
        if ($this->prototype instanceof Contracts\Model) {
            return clone $this->prototype;
        }

        // create prototype from model class name if one was set
        if (\is_string($this->model) && \class_exists($this->model)) {
            return new $this->model;
        }

        // create anon object class to create rows from
        return new class() implements Contracts\Model {
            protected array $attributes = [];
            public function __get(string $name): ?string
            {
                return $this->attributes[$name] ?? null;
            }
            public function __set(string $name, ?string $value): void
            {
                $this->attributes[$name] = $value;
            }
        };
    }

    /**
     * Create a model entity from database row
     */
    protected function model(array $attributes): Contracts\Model
    {
        $model = $this->createModel();

        if (method_exists($model, '__set')) {
            foreach ($attributes as $name => $value) {
                $model->{$name} = $value;
            }
            return $model;
        }

        $props = $this->getModelProps($model);

        foreach ($props as $prop => $default) {
            $model->{$prop} = \array_key_exists($prop, $attributes) ? $attributes[$prop] : $default;
        }

        return $model;
    }
}
