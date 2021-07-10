# Doctrine DBAL wrapper as a basic Table Gateway pattern

This is not a full orm, this is only a tribute.

Usage

```php
class Product
{
    protected string $uuid;

    protected string $desc;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getDesc(): string
    {
        return $this->desc;
    }
}

class Products extends Codin\DBAL\TableGateway
{
    protected string $table = 'products';

    protected string $primary = 'uuid';

    protected string $model = Product::class;
}

$conn = new Doctrine\DBAL\Connection(['pdo' => new PDO('sqlite:products.db')]);
$conn->exec('create table if not exists products (uuid string, desc string)');

$products = new Products($conn);
$products->insert(['desc' => 'banana', 'uuid' => 'BAN01']);
$banana = $products->fetch();
echo $product->getDesc(); // "banana"
```
