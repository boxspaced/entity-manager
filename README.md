# EntityManager

A simple to configure (but limited compared to Doctrine) PHP entity manager. It has the ability to accept custom mapper strategies, thus allowing your entities to be mapped to various data sources e.g. SOAP service, if you have to.

## Basic usage

```php
use Boxspaced\EntityManager\EntityManager;
use Boxspaced\EntityManager\Entity\AbstractEntity;

class Order extends AbstractEntity
{

    public function getId()
    {
        return $this->get('id');
    }

    public function getDate()
    {
        return $this->get('date');
    }

    public function setDate(DateTime $date)
    {
        return $this->set('date', $date);
    }

    public function getItems()
    {
        return $this->get('items');
    }

    public function addItem(OrderItem $item)
    {
        $item->setOrder($this);
        $this->get('items')->add($item);
    }

}

class OrderItem extends AbstractEntity
{

    public function getId()
    {
        return $this->get('id');
    }

    public function getDescription()
    {
        return $this->get('description');
    }

    public function setDescription($description)
    {
        return $this->set('description', $description);
    }

    public function getOrder()
    {
        return $this->get('order');
    }

    public function setOrder(Order $order)
    {
        return $this->set('order', $order);
    }

}

$config = [
    'db' => [
        // Uses zend-db internally so any driver/platform supported
        'driver' => 'Pdo_Mysql',
        'database' => '',
        'username' => '',
        'password' => '',
        'hostname' => '',
    ],
    'types' => [
        Order::class => [
            'mapper' => [
                'params' => [
                    'table' => 'orders',
                ],
            ],
            'entity' => [
                'fields' => [
                    'id' => [
                        'type' => AbstractEntity::TYPE_INT,
                    ],
                    'date' => [
                        'type' => AbstractEntity::TYPE_DATETIME,
                    ],
                ],
                'one_to_many' => [
                    'items' => [
                        'type' => OrderItem::class,
                    ],
                ],
            ],
        ],
        OrderItem::class => [
            'mapper' => [
                'params' => [
                    'table' => 'order_items',
                    'columns' => [
                        // Field to column mapping if different or a reference
                        'order' => 'order_id',
                    ],
                ],
            ],
            'entity' => [
                'fields' => [
                    'id' => [
                        'type' => AbstractEntity::TYPE_INT,
                    ],
                    'description' => [
                        'type' => AbstractEntity::TYPE_STRING,
                    ],
                    'order' => [
                        'type' => Order::class,
                    ],
                ],
            ],
        ],
    ],
];

$em = new EntityManager($config);

$order = $em->find(Order::class, 198);

$query = $em->createQuery()->field('description')->eq('Widget 500');
$orderItem = $em->findOne(OrderItem::class, $query);

$query = $em->createQuery()->field('description')->eq('Widget 600');
$orderItems = $em->findAll(OrderItem::class, $query);

foreach ($orderItems as $item) {
    // Iterate over collection
}

$item = $em->createEntity(OrderItem::class);
$item->setDescription('Widget 200');
$em->persist($item);

$order = $em->createEntity(Order::class);
$order->setDate(new DateTime());
$order->addItem($item);
$em->persist($order);

$em->flush();

$em->delete($item); // Can be avoided with cascading deletes
$em->delete($order);

$em->flush();
```

## Advanced usage

Please see the [examples](examples/) provided for advanced usage e.g. custom mapper strategies.
