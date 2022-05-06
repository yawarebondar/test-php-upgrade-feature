<?php
/**
 * Created by PhpStorm.
 * User: v.teraz
 * Date: 31.03.14
 * Time: 10:27
 */

/**
 * @see Zend_Db_Adapter_Abstract
 */
require_once "Zend/Db/Adapter/Abstract.php";

/**
 * @see Zend_Test_DbStatement
 */
require_once "Zend/Test/DbStatement.php";

/**
 * @see Zend_Db_Profiler
 */
require_once 'Zend/Db/Profiler.php';

class Yaware_Zend_Pdo_Mysqlmock extends Zend_Test_DbAdapter
{

    public static $value = array();

    public static $query = array();

    public static $returnValue = null;

    public function __construct($config)
    {
        parent::__construct();

        if (empty($config))
            return;
        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($config)) {
            /*
             * Convert Zend_Config argument to a plain array.
             */
            if ($config instanceof Zend_Config) {
                $config = $config->toArray();
            } else {
                /**
                 * @see Zend_Db_Adapter_Exception
                 */
                require_once 'Zend/Db/Adapter/Exception.php';
                throw new Zend_Db_Adapter_Exception('Adapter parameters must be in an array or a Zend_Config object');
            }
        }

        $this->_checkRequiredOptions($config);

        $options = array(
            Zend_Db::CASE_FOLDING           => $this->_caseFolding,
            Zend_Db::AUTO_QUOTE_IDENTIFIERS => $this->_autoQuoteIdentifiers
        );
        $driverOptions = array();

        /*
         * normalize the config and merge it with the defaults
         */
        if (array_key_exists('options', $config)) {
            // can't use array_merge() because keys might be integers
            foreach ((array) $config['options'] as $key => $value) {
                $options[$key] = $value;
            }
        }
        if (array_key_exists('driver_options', $config)) {
            if (!empty($config['driver_options'])) {
                // can't use array_merge() because keys might be integers
                foreach ((array) $config['driver_options'] as $key => $value) {
                    $driverOptions[$key] = $value;
                }
            }
        }

        if (!isset($config['charset'])) {
            $config['charset'] = null;
        }

        if (!isset($config['persistent'])) {
            $config['persistent'] = false;
        }

        $this->_config = array_merge($this->_config, $config);
        $this->_config['options'] = $options;
        $this->_config['driver_options'] = $driverOptions;


        // obtain the case setting, if there is one
        if (array_key_exists(Zend_Db::CASE_FOLDING, $options)) {
            $case = (int) $options[Zend_Db::CASE_FOLDING];
            switch ($case) {
                case Zend_Db::CASE_LOWER:
                case Zend_Db::CASE_UPPER:
                case Zend_Db::CASE_NATURAL:
                    $this->_caseFolding = $case;
                    break;
                default:
                    /** @see Zend_Db_Adapter_Exception */
                    require_once 'Zend/Db/Adapter/Exception.php';
                    throw new Zend_Db_Adapter_Exception('Case must be one of the following constants: '
                        . 'Zend_Db::CASE_NATURAL, Zend_Db::CASE_LOWER, Zend_Db::CASE_UPPER');
            }
        }

        // obtain quoting property if there is one
        if (array_key_exists(Zend_Db::AUTO_QUOTE_IDENTIFIERS, $options)) {
            $this->_autoQuoteIdentifiers = (bool) $options[Zend_Db::AUTO_QUOTE_IDENTIFIERS];
        }

        // obtain allow serialization property if there is one
        if (array_key_exists(Zend_Db::ALLOW_SERIALIZATION, $options)) {
            $this->_allowSerialization = (bool) $options[Zend_Db::ALLOW_SERIALIZATION];
        }

        // obtain auto reconnect on unserialize property if there is one
        if (array_key_exists(Zend_Db::AUTO_RECONNECT_ON_UNSERIALIZE, $options)) {
            $this->_autoReconnectOnUnserialize = (bool) $options[Zend_Db::AUTO_RECONNECT_ON_UNSERIALIZE];
        }

        // create a profiler object
        $profiler = false;
        if (array_key_exists(Zend_Db::PROFILER, $this->_config)) {
            $profiler = $this->_config[Zend_Db::PROFILER];
            unset($this->_config[Zend_Db::PROFILER]);
        }
        $this->setProfiler($profiler);
    }


    public function clearStack()
    {
        $this->_statementStack = array();
    }

    protected function _connect()
    {
        $this->_connection = Doctrine_Manager::connection()->getDbh();
        $this->_connected = true;
    }

    public function query($sql, $bind = array())
    {
        array_push($this::$query, $sql);
        return parent::query($sql);
    }

    public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
        array_push($this::$query, $sql);
        return parent::fetchAll($sql);
    }

    public function fetchCol($sql, $bind = array())
    {
        if (isset($bind[0]))
            str_replace("?", $sql, $bind[0]);
        array_push($this::$query, $sql);
        return parent::fetchCol($sql);
    }

    public function fetchOne($sql, $bind = array())
    {
        array_push($this::$query, $sql);
        return parent::fetchOne($sql);
    }

    public function fetchRow($sql, $bind = array(), $fetchMode = null)
    {
        return parent::fetchRow($sql, $bind, $fetchMode); // TODO: Change the autogenerated stub
    }


    public function getCount()
    {
        return count($this->_statementStack);
    }

    public static function append($value)
    {

        array_push(self::$value, $value);
    }

} 