<?php
namespace Boxspaced\EntityManager\Mapper;

use Pimple\Container;
use UnexpectedValueException;
use InvalidArgumentException;

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
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $type
     * @return Mapper
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     */
    public function createForType($type)
    {
        if (!isset($this->container['config']->types->{$type})) {
            throw new InvalidArgumentException("Config not found for type: {$type}");
        }

        $config = $this->container['config']->types->{$type};

        $strategy = null;

        if (
            isset($config->mapper->strategy)
            && is_callable($config->mapper->strategy)
        ) {
            $strategy = call_user_func($config->mapper->strategy);
        }

        if (null === $strategy) {

            if (null === $this->container['db']) {
                throw new UnexpectedValueException('Defaulting to SQL mapper but no database available');
            }

            $strategy = new SqlMapperStrategy(
                $this->container['db'],
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
