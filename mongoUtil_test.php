<?php
/**
 * Creator: donovanliu
 *
 * Test methods for MongoUtil
 */

require_once './mongoUtil_open.php';

function getInstanceTest() {
    $conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);
    var_dump($conn);
}

function insertTest() {
    $conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);
    $doc = array(
        'name' => '奶茶',
        'price' => 6,
        'status' => 1
    );

    $cnt = $conn->insert($doc);
    var_dump($cnt);
}

function batchInsertTest() {
    $conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);
    $docs = array(
        array(
            'name' => '珍珠奶茶',
            'price' => 8,
            'status' => 1
        ),
        array(
            'name' => '仙草奶茶',
            'price' => 8,
            'status' => 1
        )
    );

    $cnt = $conn->batchInsert($docs);
    var_dump($cnt);
}

function updateTest() {
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
    var_dump($cnt);
}

function removeTest() {
    $conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);
    $criteria = array(
        'name' => '奶茶'
    );

    $cnt = $conn->remove($criteria);
    var_dump($cnt);
}

function findTest() {
    $conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);
    $query = array(
        'status' => 1
    );

    $cursor = $conn->find($query);
    var_dump($cursor);

    $docs = array();
    foreach ($cursor as $doc) {
        $docs[] = $doc;
    }
    var_dump($docs);
}

function findOneTest() {
    $conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);
    $query = array(
        'status' => 1
    );

    $doc = $conn->findOne($query);
    var_dump($doc);
}

function countTest() {
    $conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);
    $query = array(
        'status' => 1
    );

    $cnt = $conn->count($query);
    var_dump($cnt);
}

function sortTest() {
    $conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);
    $filters = array(
        'status' => 1
    );
    $rule = array(
        'price' => -1
    );

    $docs = $conn->find($filters)->sort($rule);
    var_dump($docs);
}

function skipTest() {
    $conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);
    $filters = array(
        'status' => 1
    );

    $cursor = $conn->find($filters)->skip(1);
    var_dump($cursor);

    $docs = array();
    foreach ($cursor as $doc) {
        $docs[] = $doc;
    }
    var_dump($docs);
}

function limitTest() {
    $conn = MongoUtil::getInstance('test', 'goods', MongoUtil::VERSION_MONGODB);
    $filters = array(
        'status' => 1
    );

    $cursor = $conn->find($filters)->limit(1);
    var_dump($cursor);

    $docs = array();
    foreach ($cursor as $doc) {
        $docs[] = $doc;
    }
    var_dump($docs);
}

function shortMongoIdTest() {
    $mongoId = MongoUtil::shortMongoId(MongoUtil::MONGOID_8);
    var_dump($mongoId);
}

//getInstanceTest();
//insertTest();
//batchInsertTest();
//updateTest();
//removeTest();
//findTest();
//findOneTest();
//countTest();
//sortTest();
//skipTest();
//limitTest();
shortMongoIdTest();
