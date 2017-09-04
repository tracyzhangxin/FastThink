<?php
namespace Common\Lib;

class SpMongo
{
    private static $instanceof = NULL;
    public $mongo;
    private $host = 'localhost';
    private $port = '27017';

    public $db;
    public $dbname;
    public $table = NULL;
    public $collection;

    /**
     * 初始化类，得到mongo的实例对象
     */
    public function __construct($host = NULL, $port = NULL, $dbname = NULL, $table = NULL)
    {

        if (NULL === $dbname) {
            $this->throwError('集合不能为空！');
        }else {
	        $this->dbname = $dbname;
        }

        //判断是否传递了host和port
        if (NULL !== $host) {
            $this->host = $host;
        }

        if (NULL !== $port) {
            $this->port = $port;
        }

        $this->table = $table;

        //$this->mongo = new \MongoClient($this->host . ':' . $this->port);
        $this->mongo = new \Mongo('mongodb://' . $this->host . ':' . $this->port);
        if ($this->getVersion() >= '0.9.0') {
            $this->db = $this->mongo->selectDB($dbname);
            $this->collection = $this->db->selectCollection($table);
        }else {
            $this->collection = $this->mongo->$dbname->$table;
        }
    }

    public function getVersion()
    {
        return \MongoClient::VERSION;
    }

    /**
     * 单例模式
     * @return Mongo|null
     */
    public static function getInstance($host=null, $port=null, $dbname=null, $table=null){
    
       if(!(self::$instanceof instanceof self)){
           self::$instanceof = new self($host, $port, $dbname, $table);
       }
    
       return self::$instanceof;
    }

    /**
     * 插入一条数据
     * @param array $doc
     */
    public function insert($doc = array())
    {
        if (empty($doc)) {
            $this->throwError('插入的数据不能为空！');
        }
        //保存数据信息
        try {
            if (!$this->collection->insert($doc)) {
                throw new MongoException('插入数据失败');
            }
        } catch (MongoException $e) {
            $this->throwError($e->getMessage());
        }
    }

    /**
     * 插入多条数据信息
     * @param array $doc
     */
    public function insertMulti($doc = array())
    {
        if (empty($doc)) {
            $this->throwError('插入的数据不能为空！');
        }
        //插入数据信息
        foreach ($doc as $key => $val) {
            //判断$val是不是数组
            if (is_array($val)) {
                $this->insert($val);
            }
        }
    }

    /**
     * 查找一条记录
     * @return array|null
     */
    public function findOne($where = NULL)
    {
        if (NULL === $where) {
            try {
                if ($result = $this->collection->findOne()) {
                    return $result;
                } else {
                    throw new MongoException('查找数据失败');
                }
            } catch (MongoException $e) {
                $this->throwError($e->getMessage());
            }
        } else {
            try {
                if ($result = $this->collection->findOne($where)) {
                    return $result;
                } else {
                    throw new MongoException('查找数据失败');
                }
            } catch (MongoException $e) {
                $this->throwError($e->getMessage());
            }
        }

    }

    /**
     * todo 带条件的随后做
     * 查找所有的文档
     * @return MongoCursor
     */
    public function find($where = NULL)
    {
        if (NULL === $where) {

            try {
                if ($result = $this->collection->find()) {

                } else {
                    throw new MongoException('查找数据失败');
                }
            } catch (MongoException $e) {
                $this->throwError($e->getMessage());
            }
        } else {
            try {
                if ($result = $this->collection->find($where)) {

                } else {
                    throw new MongoException('查找数据失败');
                }
            } catch (MongoException $e) {
                $this->throwError($e->getMessage());
            }
        }

        $arr = array();
        foreach ($result as $id => $val) {
            $arr[] = $val;
        }

        return $arr;
    }

    /**
     * 获取记录条数
     * @return int
     */
    public function getCount()
    {
        try {
            if ($count = $this->collection->count()) {
                return $count;
            } else {
                throw new MongoException('查找总数失败');
            }
        } catch (MongoException $e) {
            $this->throwError($e->getMessage());
        }
    }

    /**
     * 获取所有的数据库
     * @return array
     */
    public function getDbs()
    {
        return $this->mongo->listDBs();
    }

    /**
     * 删除数据库
     * @param null $dbname
     * @return mixed
     */
    public function dropDb($dbname = NULL)
    {
        if (NULL !== $dbname) {
            $retult = $this->mongo->dropDB($dbname);
            if ($retult['ok']) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        $this->throwError('请输入要删除的数据库名称');
    }

    /**
     * 强制关闭数据库的链接
     */
    public function closeDb()
    {
        $this->mongo->close(TRUE);
    }

    /**
     * 输出错误信息
     * @param $errorInfo 错误内容
     */
    public function throwError($errorInfo='')
    {
        echo "<h3>出错了：$errorInfo</h3>";
        die();
    }

}