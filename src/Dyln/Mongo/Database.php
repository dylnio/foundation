<?php

namespace Dyln\Mongo;

use Dyln\DI\EventEmitterAwareInterface;
use Dyln\Event\Emitter;
use MongoDB\Driver\Exception\RuntimeException as DriverRuntimeException;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnsupportedException;
use MongoDB\Operation\CreateCollection;
use MongoDB\Operation\DropCollection;
use MongoDB\Operation\DropDatabase;

class Database extends \MongoDB\Database implements EventEmitterAwareInterface
{
    private static $defaultTypeMap = [
        'array'    => 'MongoDB\Model\BSONArray',
        'document' => 'MongoDB\Model\BSONDocument',
        'root'     => 'MongoDB\Model\BSONDocument',
    ];
    private static $wireVersionForWritableCommandWriteConcern = 5;

    private $databaseName;
    private $manager;
    private $readConcern;
    private $readPreference;
    private $typeMap;
    private $writeConcern;
    /** @var Emitter */
    protected $emitter;

    public function __construct(Manager $manager, $databaseName, array $options = [])
    {
        parent::__construct($manager, $databaseName, $options);
        if (strlen($databaseName) < 1) {
            throw new InvalidArgumentException('$databaseName is invalid: ' . $databaseName);
        }
        if (isset($options['readConcern']) && !$options['readConcern'] instanceof ReadConcern) {
            throw InvalidArgumentException::invalidType('"readConcern" option', $options['readConcern'], 'MongoDB\Driver\ReadConcern');
        }
        if (isset($options['readPreference']) && !$options['readPreference'] instanceof ReadPreference) {
            throw InvalidArgumentException::invalidType('"readPreference" option', $options['readPreference'], 'MongoDB\Driver\ReadPreference');
        }
        if (isset($options['typeMap']) && !is_array($options['typeMap'])) {
            throw InvalidArgumentException::invalidType('"typeMap" option', $options['typeMap'], 'array');
        }
        if (isset($options['writeConcern']) && !$options['writeConcern'] instanceof WriteConcern) {
            throw InvalidArgumentException::invalidType('"writeConcern" option', $options['writeConcern'], 'MongoDB\Driver\WriteConcern');
        }
        $this->manager = $manager;
        $this->databaseName = (string) $databaseName;
        $this->readConcern = isset($options['readConcern']) ? $options['readConcern'] : $this->manager->getReadConcern();
        $this->readPreference = isset($options['readPreference']) ? $options['readPreference'] : $this->manager->getReadPreference();
        $this->typeMap = isset($options['typeMap']) ? $options['typeMap'] : self::$defaultTypeMap;
        $this->writeConcern = isset($options['writeConcern']) ? $options['writeConcern'] : $this->manager->getWriteConcern();
    }

    public function createCollection($collectionName, array $options = [])
    {
        if (!isset($options['typeMap'])) {
            $options['typeMap'] = $this->typeMap;
        }
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));
        if (!isset($options['writeConcern']) && \MongoDB\server_supports_feature($server, self::$wireVersionForWritableCommandWriteConcern)) {
            $options['writeConcern'] = $this->writeConcern;
        }
        $operation = new CreateCollection($this->databaseName, $collectionName, $options);

        return $operation->execute($server);
    }

    /**
     * Drop this database.
     *
     * @see DropDatabase::__construct() for supported options
     * @param array $options Additional options
     * @return array|object Command result document
     * @throws UnsupportedException if options are unsupported on the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException for other driver errors (e.g. connection errors)
     */
    public function drop(array $options = [])
    {
        if (!isset($options['typeMap'])) {
            $options['typeMap'] = $this->typeMap;
        }
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));
        if (!isset($options['writeConcern']) && \MongoDB\server_supports_feature($server, self::$wireVersionForWritableCommandWriteConcern)) {
            $options['writeConcern'] = $this->writeConcern;
        }
        $operation = new DropDatabase($this->databaseName, $options);

        return $operation->execute($server);
    }

    /**
     * Drop a collection within this database.
     *
     * @see DropCollection::__construct() for supported options
     * @param string $collectionName Collection name
     * @param array $options Additional options
     * @return array|object Command result document
     * @throws UnsupportedException if options are unsupported on the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException for other driver errors (e.g. connection errors)
     */
    public function dropCollection($collectionName, array $options = [])
    {
        if (!isset($options['typeMap'])) {
            $options['typeMap'] = $this->typeMap;
        }
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));
        if (!isset($options['writeConcern']) && \MongoDB\server_supports_feature($server, self::$wireVersionForWritableCommandWriteConcern)) {
            $options['writeConcern'] = $this->writeConcern;
        }
        $operation = new DropCollection($this->databaseName, $collectionName, $options);

        return $operation->execute($server);
    }

    public function setEmitter(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    public function getEmitter()
    {
        return $this->emitter;
    }

    public function selectCollection($collectionName, array $options = [])
    {
        $options += [
            'readConcern'    => $this->readConcern,
            'readPreference' => $this->readPreference,
            'typeMap'        => $this->typeMap,
            'writeConcern'   => $this->writeConcern,
        ];
        $collection = new Collection($this->manager, $this->databaseName, $collectionName, $options);
        $collection->setEmitter($this->emitter);

        return $collection;
    }
}
