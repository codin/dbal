<?php

use Codin\DBAL\Contracts;

class MockModel implements Contracts\Model
{
    public string $uuid;
    public string $sku;
    public string $desc;
    public string $qty;
}
