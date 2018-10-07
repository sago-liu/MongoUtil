<?php
/**
 * Creator: donovanliu
 *
 * Just let mongodb be like mongo
 */

class MongodbClient
{
    /**
     * @var
     */
    private $instance;

    /**
     * MongodbClient constructor.
     * @param $server
     */
    public function __construct($server) {
        $this->instance['conn'] = new MongoDB\Driver\Manager($server);
    }

    /**
     * @param $db
     * @return MongodbDB
     */
    public function __get($db) {
        return new MongodbDB($this->instance['conn'], $db);
    }
}

class MongodbDB
{
    /**
     * @var
     */
    private $instance;

    /**
     * MongodbDB constructor.
     * @param $conn
     * @param $db
     */
    public function __construct($conn, $db) {
        $this->instance['conn'] = $conn;
        $this->instance['db'] = $db;
    }

    /**
     * @param $collection
     * @return MongodbCollection
     */
    public function __get($collection) {
        return new MongodbCollection($this->instance['conn'], $this->instance['db'], $collection);
    }
}

class MongodbCollection
{
    /**
     * @var
     */
    private $instance;

    /**
     * MongodbCollection constructor.
     * @param $conn
     * @param $db
     * @param $collection
     */
    public function __construct($conn, $db, $collection) {
        $this->instance['conn'] = $conn;
        $this->instance['db'] = $db;
        $this->instance['collection'] = $collection;
        $this->instance['bulkWrite'] = new MongoDB\Driver\BulkWrite();
        $this->instance['writeConcern'] = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);

        return $this->instance;
    }

    /**
     * Inserts an array into the collection
     * @param $a
     * @param array $options
     * @return mixed
     */
    public function insert($a, array $options = array()) {
        $this->instance['bulkWrite']->insert($a);
        $result = $this->instance['conn']->executeBulkWrite(
            $this->instance['db'] . '.' . $this->instance['collection'], $this->instance['bulkWrite'], $this->instance['writeConcern']);
        return $result->getInsertedCount();
    }

    /**
     * Inserts multiple documents into this collection
     * @param array $a
     * @param array $options
     * @return mixed
     */
    public function batchInsert(array $a, array $options = array()) {
        foreach ($a as $doc) {
            $this->instance['bulkWrite']->insert($doc);
        }
        $result = $this->instance['conn']->executeBulkWrite(
            $this->instance['db'] . '.' . $this->instance['collection'], $this->instance['bulkWrite'], $this->instance['writeConcern']);
        return $result->getInsertedCount();
    }

    /**
     * Update records based on a given criteria
     * @param array $criteria
     * @param array $newobj
     * @param array $options
     * @return mixed
     */
    public function update(array $criteria , array $newobj, array $options = array()) {
        $this->instance['bulkWrite']->update($criteria, $newobj);
        $result = $this->instance['conn']->executeBulkWrite(
            $this->instance['db'] . '.' . $this->instance['collection'], $this->instance['bulkWrite'], $this->instance['writeConcern']);
        return $result->getModifiedCount();
    }

    /**
     * Removes files from the collections
     * @param array $criteria
     * @param array $options
     * @return mixed
     */
    public function remove(array $criteria = array(), array $options = array()) {
        $this->instance['bulkWrite']->delete($criteria);
        $result = $this->instance['conn']->executeBulkWrite(
            $this->instance['db'] . '.' . $this->instance['collection'], $this->instance['bulkWrite'], $this->instance['writeConcern']);
        return $result->getDeletedCount();
    }

    /**
     * Querys this collection
     * @param array $query
     * @param array $fields
     * @return MongodbCursor
     */
    public function find(array $query = array(), array $fields = array()) {
        $query = new MongoDB\Driver\Query($query);
        $cursor = $this->instance['conn']->executeQuery(
            $this->instance['db'] . '.' . $this->instance['collection'], $query);

        $docs = array();
        foreach ($cursor as $doc) {
            $docs[] = (array)$doc;
        }
        return new MongodbCursor($docs);
    }

    /**
     * Querys this collection, returning a single element
     * @param array $query
     * @param array $fields
     * @return array|null
     */
    public function findOne(array $query = array(), array $fields = array()) {
        $query = new MongoDB\Driver\Query($query);
        $cursor = $this->instance['conn']->executeQuery(
            $this->instance['db'] . '.' . $this->instance['collection'], $query);

        $doc = null;
        foreach ($cursor as $doc) {
            $doc = (array)$doc;
            break;
        }
        return $doc;
    }

    /**
     * Counts the number of documents in this collection
     * @param array $query
     * @return int
     */
    public function count($query = array()) {
        $query = new MongoDB\Driver\Query($query);
        $cursor = $this->instance['conn']->executeQuery(
            $this->instance['db'] . '.' . $this->instance['collection'], $query);

        $docs = array();
        foreach ($cursor as $doc) {
            $docs[] = (array)$doc;
        }
        return count($docs);
    }
}

class MongodbCursor implements Iterator
{
    /**
     * @var
     */
    private $list;
    /**
     * @var
     */
    private $pos;
    /**
     * @var int
     */
    private $origin;
    /**
     * @var int
     */
    private $destination;
    /**
     * @var
     */
    private static $sortRule;

    /**
     * MongodbCursor constructor.
     * @param $docs
     */
    public function __construct($docs) {
        $this->list = $docs;
        $this->origin = 0;
        $this->destination = count($docs);
    }

    /**
     * Returns the current element
     * @return mixed
     */
    public function current() {
        return $this->list[$this->pos];
    }

    /**
     * Returns the current result's _id
     * @return mixed
     */
    public function key() {
        return $this->pos;
    }

    /**
     * Advances the cursor to the next result
     */
    public function next() {
        ++$this->pos;
    }

    /**
     * Returns the cursor to the beginning of the result set
     */
    public function rewind() {
        $this->pos = $this->origin;
    }

    /**
     * Checks if the cursor is reading a valid result.
     * @return bool
     */
    public function valid() {
        return ($this->pos < $this->destination);
    }

    /**
     * Sorts the results by given fields
     * @param $rule
     * @return mixed
     */
    public function sort($rule) {
        $rule = (array)$rule;

        self::$sortRule = $rule;
        usort($this->list, array('MongodbCursor', 'cmp'));
        return $this->list;
    }

    /**
     * Skips a number of results
     * @param $num
     * @return $this
     */
    public function skip($num) {
        $num = intval($num);

        $this->origin = $num;
        return $this;
    }

    /**
     * Limits the number of results returned
     * @param $num
     * @return $this
     */
    public function limit($num) {
        $num = intval($num);

        $this->destination = $this->origin + $num;
        return $this;
    }

    /**
     * @param $a
     * @param $b
     * @return float|int
     */
    private static function cmp($a, $b) {
        $key = key(self::$sortRule);
        $postive = self::$sortRule[$key];

        if ($a[$key] == $b[$key]) {
            return 0;
        }
        return ($a[$key] < $b[$key]) ? (-1 * $postive) : (1 * $postive);
    }
}