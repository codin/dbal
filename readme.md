# Doctrine DBAL wrapper as a basic Table Gateway pattern

This is not a full orm, this is only a tribute.

Usage

```php
class Product
{
    public string $uuid;

    public string $sku;

    public string $desc;
}

class Products extends Codin\DBAL\TableGateway
{
    protected string $table = 'products';

    protected string $primary = 'uuid';

    protected string $model = Product::class;
}

$conn = new Doctrine\DBAL\Connection(['pdo' => new PDO('sqlite:products.db')]);
$conn->exec('create table if not exists products (uuid string, sku string, desc string)');

$products = new Products($conn);

// create from array
// or with assignments $banana->desc = ...
$banana = new Product(['desc' => 'banana', 'sku' => 'ban1', 'uuid' => 'BAN01']);
$products->persist($banana);

$banana = $products->fetch();
echo $product->desc; // "banana"

$banana->desc = 'purple banana';
$products->persist($banana);
```
