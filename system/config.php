<?php
namespace System;
use System\Support\Arr;
//======================================================================
// CONFIG
//======================================================================
/**
 * @package    Config
 * @author     Mahmoud Elnezamy <mahmoudelnezamy@gmail.com>
 * @link       https://github.com/nezamy/config
 * @license    MIT
 */

class Config
{
	private static $instance;
	/**
     * @var array Cached file name, check if already loaded before
     */
	private $_cache = [];

	/**
     * @var array Cached key name
     */
	private $_cacheKey = [];

	/**
     * @var array Stores the configuration data
     */
	private $_data = [];

	/**
     * Static method for loading a Config instance - Singleton.
     *
     * @param  string $p the file path
     *
     * @return Config object
     */
	public static function load($p)
    {
        if (null === static::$instance) {
            static::$instance = new static($p);
        }
        return static::$instance->append($p);
    }

    /**
     * Constructor
     *
     * @param  string $p the file path or data
     * @param  string $p the file path or data
     */
	public function __construct($p, $data = false)
    {
        if($data){
    		$this->appendData((array) $p);
        }else{
            $this->append($p);
        }
	}

	/**
     * append data from files & merge data .
     *
     * @param  string $path the file path
     *
     * @return Config object
     */
	public function append($path)
	{
    	//check if cached
    	if( !in_array($path, $this->_cache) )
    	{
			$p =  new \SplFileInfo($path);
			if($p->isFile() && $p->isReadable())
			{
				try {
					$data = $this->loadParser( $p->getExtension(), $p->getPathname() );
					$this->_cache[]	= $path;
					$this->_data	=  array_merge($this->_data, $data);
				} catch (\Exception $e) {
					echo $e->getMessage();
				}
			}else{
                throw new \Exception("file ( $path ) not exsists");
			}
        }
    	return $this;
	}

    /**
     * append data from array & merge data .
     *
     * @param  string $data the file path
     *
     * @return Config object
     */
    public function appendData(array $data)
    {
        $this->_data    =  array_merge($this->_data, $data);
        return $this;
    }

	/**
     * get data by key or nested keys .
     *
     * @param  string $k key name or nested key example 'parent.child.key'
     * @param  string $default if key not isset return default value.
     *
     * @return mixed value
     */
	public function get($k=null)
	{
        if($k == null){
            return $this->_data;
        }

		// Check if key already cached
        if (isset($this->_cacheKey[$k])) {
            return $this->_cacheKey[$k];
        }

        //caching key & return data
		return ( $this->_cacheKey[$k] = Arr::get($this->_data, $k) );
	}

	/**
     * check & load parser if supported .
     *
     * @param  string $ext file extension
     * @param  string $p file include path
     *
     * @return array
     */
	private function loadParser($ext, $p){
		switch ($ext) {
			case 'php':
				return $this->phpParse($p);
			case 'json':
				return $this->jsonParse($p);
			case 'xml':
				return $this->xmlParse($p);
			case 'ini':
				return $this->iniParse($p);
			default:
				throw new \Exception('File extension ('. $ext .') not supported');
		}
	}

	/**
     * PHP Parser
     *
     * @param  string $p file include path
     *
     * @return array
     */
	private function phpParse($p){
		return (array) require $p;
	}

	/**
     * JSON parser
     *
     * @param  string $p file include path
     *
     * @return array
     */
	private function jsonParse($p){
		return json_decode( file_get_contents($p), true);
	}

	/**
     * XML Parser
     *
     * @param  string $p file include path
     *
     * @return array
     */
	private function xmlParse($p){
		$data = simplexml_load_file($p, null, LIBXML_NOERROR);
		return json_decode(json_encode($data), true);
	}

	/**
     * INI Parser
     *
     * @param  string $p file include path
     *
     * @return array
     */
	private function iniParse($p){
		return parse_ini_file($p, true);
	}

}


// $c = Config::load('config.php');
// $c = Config::load('config.json');
// $c = Config::load('config.xml');
// $c = Config::load('config.ini');
// echo $c->get('db.dbname');
