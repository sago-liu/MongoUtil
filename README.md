## MongoUtil

Welcome to MongoUtil!

#### What is MongoUtil?
The mongo extension is no longer supported after PHP 5.
Mongodb extension，the replacement of mongo extension，is not a very friendly package.
Furthermore，it brings a certain cost of migration.

MongoUtil is a gadget that let mongodb be like mongo. 
It also provides some other useful features such as short id.
In the future, it will be further enriched.

#### How to use MongoUtil?

- connect
```php
$db = 'test';
$collection = 'goods';

// PHP 5
$conn = MongoUtil::getInstance($db, $collection);

// PHP 7
$conn = MongoUtil::getInstance($db, $collection, MongoUtil::VERSION_MONGODB);
```

- insert
```php
$conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);

$doc = array(
    'name' => '奶茶',
    'price' => 6,
    'status' => 1
);
$cnt = $conn->insert($doc);
```

- update
```php
$conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);

$criteria = array(
    'name' => '奶茶'
);
$doc = array(
    '$set' => array(
        'price' => 7,
    )
);
$cnt = $conn->update($criteria, $doc);
```

- remove
```php
$conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);

$criteria = array(
    'name' => '奶茶'
);
$cnt = $conn->remove($criteria);
```

- find
```php
$conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);

$query = array(
    'status' => 1
);
$cursor = $conn->find($query);

$docs = array();
foreach ($cursor as $doc) {
    $docs[] = $doc;
}
```

For more usage, please read the test cases.

#### Appendix: MongoUtil's short mongo id

- native mongo id (length: 24)

time stamp(4 bytes) + machine code(3 bytes) + PID(2 bytes) + counter(3 bytes)

base：16

- native mongo id with lossless compression (length: 16)

time stamp(4 bytes) + machine code(3 bytes) + PID(2 bytes) + counter(3 bytes)

base：64

- approximate mongo id (length: 14)

time stamp(4 bytes) + machine code(4 bytes) + random number(2 bytes)

base：64

- approximate mongo id (length: 8)

time stamp(4 bytes) + random number(2 bytes)

base：64