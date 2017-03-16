<?php
namespace Boxspaced\EntityManager\Mapper;

use Pimple\Container;
use Boxspaced\EntityManager\Exception;

class MapperFactory
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Mapper[]
     */
    protected $instances = [];

    /**
     * @var MapperStrategyInterface[]
     */
    protected $strategies = [];

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param MapperStrategyInterface $mapperStrategy
     */
    public function addMapperStrategy(MapperStrategyInterface $mapperStrategy)
    {
        $this->strategies[get_class($mapperStrategy)] = $mapperStrategy;
        return $this;
    }

    /**
     * @param string $type
     * @return Mapper
     * @throws Exception\UnexpectedValueException
     * @throws Exception\InvalidArgumentException
     */
    public function createForType($type)
    {
        if (!isset($this->container['config']['types'][$type])) {
            throw new Exception\InvalidArgumentException("Config not found for type: {$type}");
        }

        $config = $this->container['config']['types'][$type];

        if (isset($config['mapper']['strategy'])) {

            if (!isset($this->strategies[$config['mapper']['strategy']])) {

                throw new Exception\InvalidArgumentException(sprintf(
                    'Mapper strategy not found: %s',
                    $config['mapper']['strategy'])
                );
            }

            $strategy = $this->strategies[$config['mapper']['strategy']];
        }

        if (!isset($strategy)) {

            if (null === $this->container['db']) {
                throw new Exception\UnexpectedValueException('Defaulting to SQL mapper but no database available');
            }

            $strategy = new SqlMapperStrategy(
                $this->container['db'],
                new SqlSelectBuilder($this->container['config']),
                $this->container['config']
            );
        }

        $key = get_class($strategy);

        if (!isset($this->instances[$key])) {

            $this->instances[$key] = new Mapper(
                $this->container['identityMap'],
                $this->container['entityBuilder'],
                $this->container['collectionFactory'],
                $strategy
            );
        }

        return $this->instances[$key];
    }

}
