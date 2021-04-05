<?php

namespace spec\Codin\DBAL;

use Codin\DBAL\Contracts;
use Codin\DBAL\Exceptions;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use PhpSpec\ObjectBehavior;

require __DIR__ . '/mocks.php';

class TableGatewaySpec extends ObjectBehavior
{
    public function let()
    {
        $conn = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'path' => ':memory:']);
        $conn->executeStatement('create table if not exists products (
            uuid string,
            sku string unique,
            desc string,
            qty integer,
            primary key(uuid)
        )');
        $this->beConstructedWith($conn);

        $ref = new \ReflectionClass($this->getWrappedObject());

        $this->shouldThrow(Exceptions\GatewayError::class)->during('getTable');

        $prop = $ref->getProperty('table');
        $prop->setAccessible(true);
        $prop->setValue($this->getWrappedObject(), 'products');

        $this->shouldThrow(Exceptions\GatewayError::class)->during('getPrimary');

        $prop = $ref->getProperty('primary');
        $prop->setAccessible(true);
        $prop->setValue($this->getWrappedObject(), 'uuid');
    }

    public function it_should_be_setup()
    {
        $this->getConnection()->shouldBeAnInstanceOf(Connection::class);
        $this->getTable()->shouldReturn('products');
        $this->getPrimary()->shouldReturn('uuid');
        $this->getQueryBuilder()->shouldBeAnInstanceOf(QueryBuilder::class);
    }

    public function it_should_handle_disconnects()
    {
        $this->getConnection()->getWrappedObject()->close();

        $this->shouldThrow(Exceptions\GatewayError::class)->during('fetch');
    }

    public function it_should_run_commands()
    {
        $this->fetch()->shouldReturn(null);

        $this->shouldThrow(Exceptions\GatewayError::class)->during('fetch', [
            $this->getQueryBuilder()->getWrappedObject()
                ->where('foo = 1'),
        ]);

        $this->get()->shouldReturn([]);
        $this->getUnbuffered()->shouldBeAnInstanceOf(\Generator::class);
        $this->column()->shouldReturn(null);

        $uuid = '1234-5678-9012-3456';

        $this->shouldThrow(Exceptions\GatewayError::class)->during('insert', [[]]);

        $this->shouldThrow(Exceptions\GatewayError::class)->during('insert', [[
            'foo' => 'bar',
        ]]);

        $this->insert([
            'uuid' => $uuid,
            'sku' => rand(),
            'desc' => 'test',
            'qty' => 1,
        ])->shouldReturn('1');

        $result = $this->fetch();

        $result->uuid->shouldReturn($uuid);
        $result->desc->shouldReturn('test');

        $this->get()[0]->uuid->shouldReturn($uuid);

        $generator = $this->getUnbuffered();
        $generator->current()->uuid->shouldReturn($uuid);
        $generator->next();
        $generator->valid()->shouldReturn(false);

        $this->column()->shouldReturn($uuid);

        $this->shouldThrow(Exceptions\GatewayError::class)->during('update', [
            $this->getQueryBuilder()->getWrappedObject()
                ->where('foo = 1'),
        ]);

        $this->update(
            $this->getQueryBuilder()->getWrappedObject()
                ->set('desc', ':desc')
                ->setParameter('desc', 'updated test')
                ->where('uuid = :uuid')
                ->setParameter('uuid', $uuid)
        );

        $this->fetch()->desc->shouldReturn('updated test');

        $this->delete(
            $this->getQueryBuilder()->getWrappedObject()
                ->where('uuid = :uuid')
                ->setParameter('uuid', $uuid)
        );

        $this->fetch()->shouldReturn(null);
    }

    public function it_should_not_overflow()
    {
        $buffer = 1024 * 1024;
        $pad = str_repeat(' ', $buffer);

        $uuid = '1234-5678-9012-3456';

        $this->insert([
            'uuid' => $uuid,
            'sku' => rand(),
            'desc' => $pad,
            'qty' => 1,
        ])->shouldReturn('1');

        $ref = new \ReflectionClass($this->getWrappedObject());
        $prop = $ref->getProperty('memoryLimit');
        $prop->setAccessible(true);
        $prop->setValue($this->getWrappedObject(), $buffer);

        $this->shouldThrow(Exceptions\GatewayOverflow::class)->during('get');
    }

    public function it_should_run_aggregates()
    {
        $query = $this->getQueryBuilder()->getWrappedObject();

        $this->count($query, 'uuid')->shouldReturn('0');
        $this->sum($query, 'qty')->shouldReturn('0');
        $this->min($query, 'uuid')->shouldReturn('0');
        $this->max($query, 'uuid')->shouldReturn('0');

        foreach (range(1, 100) as $index) {
            $this->insert([
                'uuid' => $index,
                'sku' => rand(),
                'desc' => 'test',
                'qty' => 2,
            ]);
        }

        $this->count()->shouldReturn('100');
        $this->sum(null, 'qty')->shouldReturn('200');
        $this->min(null, 'uuid')->shouldReturn('1');
        $this->max(null, 'uuid')->shouldReturn('100');
    }

    public function it_should_work_with_prototype_models()
    {
        $model = new class() implements Contracts\Model {
            public string $uuid;
            public string $sku;
            public string $desc;
            public string $qty;
        };

        $ref = new \ReflectionClass($this->getWrappedObject());
        $prop = $ref->getProperty('prototype');
        $prop->setAccessible(true);
        $prop->setValue($this->getWrappedObject(), $model);

        $uuid = '1234-5678-9012-3456';

        $this->insert([
            'uuid' => $uuid,
            'sku' => rand(),
            'desc' => 'test',
            'qty' => 1,
        ])->shouldReturn('1');

        $result = $this->fetch();

        $result->uuid->shouldReturn($uuid);
        $result->desc->shouldReturn('test');
    }

    public function it_should_work_with_defined_models()
    {
        $ref = new \ReflectionClass($this->getWrappedObject());
        $prop = $ref->getProperty('model');
        $prop->setAccessible(true);
        $prop->setValue($this->getWrappedObject(), \MockModel::class);

        $uuid = '1234-5678-9012-3456';

        $this->insert([
            'uuid' => $uuid,
            'sku' => rand(),
            'desc' => 'test',
            'qty' => 1,
        ])->shouldReturn('1');

        $result = $this->fetch();

        $result->uuid->shouldReturn($uuid);
        $result->desc->shouldReturn('test');
    }
}
