<?php

use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Mapper\Query;
use Boxspaced\EntityManager\Mapper\MapperStrategyInterface;
use Boxspaced\EntityManager\EntityManager;

require __DIR__ . '/../vendor/autoload.php';

class Customer extends AbstractEntity
{

    public function getId()
    {
        return $this->get('id');
    }

    public function getName()
    {
        return $this->get('name');
    }

    public function setName($name)
    {
        return $this->set('name', $name);
    }

    public function getOrders()
    {
        return $this->get('orders');
    }

}

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

    public function getCustomer()
    {
        return $this->get('customer');
    }

    public function getItems()
    {
        return $this->get('items');
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

}

/**
 * Retrieve and persist customer data
 * e.g. via CRM SOAP service, but just use internal array here
 */
class CustomerMapperStrategy implements MapperStrategyInterface
{

    protected $data = [
        [
            'customerID' => 1,
            'customerName' => 'Betty Davis',
        ],
        [
            'customerID' => 2,
            'customerName' => 'John Smith',
        ],
        [
            'customerID' => 3,
            'customerName' => 'Jane Jones',
        ],
    ];

    protected $map = [
        'id' => 'customerID',
        'name' => 'customerName',
    ];

    public function find($type, $id)
    {
        $index = array_search($id, array_column($this->data, $this->map['id']));

        if (false === $index) {
            return null;
        }

        return $this->processResult($this->data[$index]);
    }

    protected function processResult($result)
    {
        $processed = [];

        foreach ($result as $key => $value) {
            $processed[array_search($key, $this->map)] = $value;
        }

        return $processed;
    }

    public function findOne($type, Query $query = null)
    {

    }

    public function findAll($type, Query $query = null)
    {

    }

    public function insert(AbstractEntity $entity)
    {

    }

    public function update(AbstractEntity $entity)
    {

    }

    public function delete(AbstractEntity $entity)
    {

    }

}

$config = [
    'db' => [
        'driver' => 'Pdo_Sqlite',
        'database' => __DIR__ . '/../examples/example.db',
    ],
    'types' => [
        Customer::class => [
            'mapper' => [
                'strategy' => CustomerMapperStrategy::class,
            ],
            'entity' => [
                'fields' => [
                    'id' => [
                        'type' => AbstractEntity::TYPE_INT,
                    ],
                    'name' => [
                        'type' => AbstractEntity::TYPE_STRING,
                    ],
                ],
                'one_to_many' => [
                    'orders' => [
                        'type' => Order::class,
                    ],
                ],
            ],
        ],
        Order::class => [
            // Will use default SQL mapper
            'mapper' => [
                'params' => [
                    'table' => 'orders',
                    'columns' => [
                        // Field to column mapping if different or a reference
                        'customer' => 'customer_id',
                    ],
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
                    'customer' => [
                        'type' => Customer::class,
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
            // Will use default SQL mapper
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
$em->addMapperStrategy(new CustomerMapperStrategy());

// Get customer (will use custom mapper strategy), then the order data from the db

echo '===========================================' . PHP_EOL;

$customer = $em->find(Customer::class, 2);

echo $customer->getName() . PHP_EOL;

echo '-------------' . PHP_EOL;

foreach ($customer->getOrders() as $order) {

    echo $order->getDate()->format('Y-m-d') . PHP_EOL;

    foreach ($order->getItems() as $item) {

        echo ' +-- ' . $item->getDescription() . PHP_EOL;
    }
}

// Get order item from db, then find related customer

echo '===========================================' . PHP_EOL;

$orderItem = $em->find(OrderItem::class, 1);

echo $orderItem->getOrder()->getCustomer()->getName() . PHP_EOL;