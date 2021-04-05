<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

class Product implements Codin\DBAL\Contracts\Model
{
    public string $uuid;

    public string $sku;

    public string $desc;
}

class Products extends Codin\DBAL\TableGateway
{
    protected string $table = 'products';

    protected string $primary = 'uuid';

    protected ?string $model = Product::class;
}

$conn = Doctrine\DBAL\DriverManager::getConnection(['driver' => 'pdo_sqlite', 'path' => 'products.sqlite']);
$conn->executeStatement('create table if not exists products (
    uuid string,
    sku string unique,
    desc string,
    primary key(uuid)
)');

$products = new Products($conn);

$banana = new Product();
$banana->uuid = 'BAN'.rand();
$banana->sku = 'ban'.rand();
$banana->desc = 'banana';

$result = $products->persist($banana);
echo "result: {$result}\n";

$product = $products->find($banana->uuid);

echo "{$product->uuid}: {$product->desc}\n";
echo "{$products->count()} products\n";

echo "\n";

class Order implements Codin\DBAL\Contracts\Model
{
    public string $id;

    public string $created_at;
}

class Orders extends Codin\DBAL\TableGateway
{
    protected string $table = 'orders';

    protected string $primary = 'id';

    //protected ?string $model = Order::class;
}

$conn->executeStatement('create table if not exists orders (
    id integer,
    created_at datetime,
    primary key(id autoincrement)
)');

$order = new Order();
$order->created_at = date('Y-m-d H:i:s');

$orders = new Orders($conn);
$result = $orders->persist($order);
echo "result: {$result}\n";

echo "id: {$order->id}\n";
$order = $orders->fetch($orders->getQueryBuilder()->orderBy('id', 'desc'));
echo "id: {$order->id}\n";

echo "{$orders->count()} orders\n";
