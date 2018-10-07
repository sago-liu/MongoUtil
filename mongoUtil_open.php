<?php
/**
 * Creator: donovanliu
 *
 * ||~PHP driver||~PHP 版本||~备注||
 * ||mongo ||PHP 5.3-5.6 ||直接使用 ||
 * ||mongodb ||PHP 5.5+ ||通过mongodbTransverter使用 ||
 */

require_once './mongodbTransverter.php';

class MongoUtil
{
    /**
     * Use mongo
     */
    const VERSION_MONGO = 0;
    /**
     * Use mongodb with mongodbTransverter
     */
    const VERSION_MONGODB = 1;
    /**
     * The length of the id
     */
    const MONGOID_8 = 8;
    /**
     * The length of the id
     */
    const MONGOID_14 = 14;
    /**
     * The length of the id
     */
    const MONGOID_16 = 16;
    /**
     * The alphabet in base 64
     * @var string
     */
    private static $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-';
    /**
     * A letter's digits in base 64
     * @var int
     */
    private static $letterDigits = 6;
    /**
     * @var
     */
    private static $instance;
    /**
     * @var array
     */
    private static $serverConfig = array(
        'username' => '',
        'password' => '',
        'ip' => '',
        'port' => ''
    );

    /**
     * @param $db
     * @param $collection
     * @param int $version
     * @return MongoCollection|MongodbCollection
     * @throws MongoConnectionException
     */
    public static function getInstance($db, $collection, $version = self::VERSION_MONGO) {
        if (self::$instance) {
            return self::$instance;
        }

        $server = self::getServer();

        switch ($version) {
            case self::VERSION_MONGO:
                self::$instance = new MongoClient($server, $options = array("connect" => TRUE), $driver_options = array());
                break;
            case self::VERSION_MONGODB:
                self::$instance = new MongodbClient($server);
                break;
        }

        return self::$instance->$db->$collection;
    }

    /**
     * @return string
     */
    private static function getServer() {
        $username = self::$serverConfig['username'];
        $password = self::$serverConfig['password'];
        $ip = self::$serverConfig['ip'];
        $port = self::$serverConfig['port'];
        if (!empty($username) && !empty($password) && !empty($ip) && !empty($port)) {
            $server = "mongodb://$username:$password@$ip:$port";
        } else {
            $server = "mongodb://localhost:27017";
        }

        return $server;
    }

    /**
     * @param $mongoId
     * @return mixed
     */
    public static function mongoId2str($mongoId) {
        return $mongoId->{'$id'};
    }

    /**
     * @param $str
     * @return MongoId
     */
    public static function str2mongoId($str) {
        $str = strval($str);

        return new MongoId($str);
    }

    /**
     * @param $long
     * @return null|string|string[]
     */
    public static function shortMongoId($len) {
        $len = intval($len);

        switch ($len) {
            case self::MONGOID_8:
                $time = base_convert(uniqid(), 10, 16);
                $rand = mt_rand(0, 255);
                $str = $time . $rand;
                break;
            case self::MONGOID_14:
                $time = base_convert(uniqid(), 10, 16);
                $machineCode = hash('CRC32', gethostname());
                $rand = mt_rand(0, 255);
                $str = $time . $machineCode . $rand;
                break;
            case self::MONGOID_16:
            default:
                $id = new MongoId();
                $str = self::mongoId2str($id);
                break;
        }

        return self::base16to64($str, $len);
    }

    /**
     * @param $str
     * @param int $len
     * @return null|string|string[]
     */
    private static function base16to64($str, $len = 0) {
        $str = strval($str);
        $len = intval($len);

        $hex2 = array();
        for($i = 0, $j = strlen($str); $i < $j; ++$i) {
            $hex2[] = str_pad(base_convert($str[$i], 16, 2), 4, '0', STR_PAD_LEFT);
        }
        $hex2 = implode('', $hex2);
        $hex2 = self::strSplit($hex2, self::$letterDigits);

        $hex64 = array();
        foreach($hex2 as $one) {
            $hex64[] = self::$alphabet[bindec($one)];
        }

        $return = preg_replace('/^0*/', '', implode('', $hex64));
        if ($len) {
            if(strlen($return) >= $len) {
                return $return;
            } else {
                return str_pad($return, $len, '0', STR_PAD_LEFT);
            }
        }
        return $return;
    }

    /**
     * @param $str
     * @param $len
     * @return array|bool
     */
    private static function strSplit($str, $len) {
        $str = strval($str);
        $len = intval($len);

        if(empty($str) || empty($len)) {
            return false;
        }

        $strlen = strlen($str);
        if($strlen <= $len) {
            return array($str);
        }

        $headlen = $strlen % $len;
        if($headlen == 0) {
            return str_split($str, $len);
        }

        $return = array(substr($str, 0, $headlen));
        return array_merge($return, str_split(substr($str, $headlen), $len));
    }
}