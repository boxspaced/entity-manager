<?php

use Boxspaced\EntityManager\EntityManager;
use Boxspaced\EntityManager\Entity\AbstractEntity;

require __DIR__ . '/../vendor/autoload.php';

class Order extends AbstractEntity
{

    public function getId()
    {
        return $this->get('id');
    }

    public function setId($id)
    {
        return $this->set('id', $id);
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

    public function setId($id)
    {
        return $this->set('id', $id);
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
        'driver' => 'Pdo_Sqlite',
        'database' => __DIR__ . '/../examples/example.db',
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

// Find an order by ID

$order = $em->find(Order::class, 1);

echo $order->getDate()->format('Y-m-d') . PHP_EOL;

foreach ($order->getItems() as $item) {
    echo ' +-- ' . $item->getDescription() . PHP_EOL;
}

// Query for orders but return just one

$query = $em->createQuery()->field('description')->eq('Car');
$orderItem = $em->findOne(OrderItem::class, $query);

echo $orderItem->getId() . PHP_EOL;

// Query for all orders returning a collection

$query = $em->createQuery()->field('description')->eq('Car');
$orderItems = $em->findAll(OrderItem::class, $query);

foreach ($orderItems as $item) {
    echo $item->getOrder()->getId() . PHP_EOL;
}

// Create an order with an order item

$item = $em->createEntity(OrderItem::class);
$item->setId(11)->setDescription('Widget for this and that');
$em->persist($item);

$order = $em->createEntity(Order::class);
$order->setId(5)->setDate(new DateTime());
$order->addItem($item);
$em->persist($order);

$em->flush();

// Delete previously created order and item

$item = $em->find(OrderItem::class, 11);
$em->delete($item);

$order = $em->find(Order::class, 5);
$em->delete($order);

$em->flush();
