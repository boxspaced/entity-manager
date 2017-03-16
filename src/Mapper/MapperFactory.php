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

        $strategy = $this->createMapperStrategy($this->container['config']['types'][$type]);

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

    /**
     * @param array $config
     * @return MapperStrategyInterface
     * @throws Exception\InvalidArgumentException
     */
    protected function createMapperStrategy($config)
    {
        if (isset($config['mapper']['strategy'])) {

            if (SqlMapperStrategy::class === $config['mapper']['strategy']) {
                return $this->createSqlMapperStrategy();
            }

            if (MongoMapperStrategy::class === $config['mapper']['strategy']) {
                return $this->createMongoMapperStrategy();
            }

            if (!isset($this->strategies[$config['mapper']['strategy']])) {

                throw new Exception\InvalidArgumentException(sprintf(
                    'Mapper strategy not found: %s',
                    $config['mapper']['strategy'])
                );
            }

            return $this->strategies[$config['mapper']['strategy']];
        }

        return $this->createSqlMapperStrategy();
    }

    /**
     * @return SqlMapperStrategy
     * @throws Exception\UnexpectedValueException
     */
    protected function createSqlMapperStrategy()
    {
        if (null === $this->container['db']) {
            throw new Exception\UnexpectedValueException('Defaulting to SQL mapper but no database available');
        }

        return new SqlMapperStrategy(
            $this->container['db'],
            $this->container['config']
        );
    }

    /**
     * @return MongoMapperStrategy
     * @throws Exception\UnexpectedValueException
     */
    protected function createMongoMapperStrategy()
    {
        return new MongoMapperStrategy(

        );
    }

}
