<?php

declare(strict_types=1);

namespace Codin\DBAL\Traits;

use ReflectionClass;
use function get_class;

trait Models
{
    /**
     * Create a new entity
     */
    protected function createModel(): object
    {
        if (is_object($this->prototype)) {
            return clone $this->prototype;
        }

        // create prototype from model class name if one was set
        if (\is_string($this->model) && \class_exists($this->model)) {
            return new $this->model;
        }

        // create anon object class to create rows from
        return new class() {
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
    protected function model(array $attributes): object
    {
        $model = $this->createModel();

        if (method_exists($model, '__set')) {
            foreach ($attributes as $name => $value) {
                $model->{$name} = $value;
            }
            return $model;
        }

        $ref = new ReflectionClass($model);
        $props = $ref->getProperties();

        foreach ($props as $prop) {
            if (array_key_exists($prop->getName(), $attributes)) {
                $prop->setAccessible(true);
                $prop->setValue($model, $attributes[$prop->getName()]);
            }
        }

        return $model;
    }
}
