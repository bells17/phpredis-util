<?php
require_once(dirname(__FILE__) . '/RedisConnectionCollecter.php');
/**
 * RedisManager
 * use phpredis
 * https://github.com/nicolasff/phpredis
 * @author hayakawa
 */
class RedisManager {

	protected $_delemiter_base 		= ':'; //baseキーの区切り文字
	protected $_delemiter_key_value = '='; ////キーバリューの区切り文字


	/* ---------- redis connection ---------- */


	/**
	 * インスタンス生成時にself::DEFAULT_DBで設定したキー名のredisの接続済みインスタンスをセットします
	 */
	public function __construct($dbs, $defaultRedisKey, $delemiter = null)
	{
		$this->setConnection($dbs, $defaultRedisKey);
		$this->setDelemiter($delemiter);
	}

	/**
	 * $this->_redisに指定したキー名でredisの接続済みインスタンスをセットします
	 */
	public function setConnection(array $dbs, $key)
	{
		foreach ($dbs as $key => $db) {
			RedisConnectionCollecter::setDbInfo(array($key => $db));
		}
	}

	/**
	 * redisのキーの区切り文字を設定します
	 */
	public function setDelemiter($delemiter = null)
	{
		if (isset($delemiter['base'])) {
			$this->_delemiter_base = $delemiter['base'];
		}
		if (isset($delemiter['key_value'])) {
			$this->_delemiter_key_value = $delemiter['key_value'];
		}
	}

	/**
	 * getConnection
	 */
	public function getConnection($key)
	{
		return RedisConnectionCollecter::getConnection($key);
	}


	/* ---------- redisキーの生成用 ---------- */


	/**
	 * redisのベースになるキー名を生成します
	 * @param $base_key_args 	ベースとなるキー名を配列で指定してください
	 * array(
	 * 		'basekey1', 'basekey2',
	 * );
	 *
	 * @param $key_value_args 	ベースとなるキーと値を連想配列で指定してください
	 * array(
	 * 		'key1' => 'value1',
	 * 		'key2' => 'value2',
	 * );
	 *
	 * @return sample. 'prefix:basekey1:basekey2:key1=value1:key2=value2'
	 */
	public function createRedisKey(array $base_key_args, array $key_value_args)
	{
		return $this->createBaseRedisKey($base_key_args) . $this->createKeyValueRedisKey($key_value_args);
	}

	/**
	 * redisのベースになるキー名を設定します
	 * @param $args ベースとなるキー名を配列で指定してください
	 * ex) array("ranking", "weekly"); => "ranking:weekly"
	 */
	public function createBaseRedisKey(array $args)
	{
		$name = $this->_delemiter_key_value;
		foreach ($args as $arg) {
			$name .= $arg . $this->_delemiter_base;
		}
		return $name;
	}

	/**
	 * ベースとなるキーの名前を返します(最後の:は付きません)
	 * @param $args ベースとなるキーと値を連想配列で指定してください
	 * ex) array("weekid" => 1, "eventid" => 3); => "weekid=1:eventid=3"
	 */
	public function createKeyValueRedisKey(array $args)
	{
		if (count($args) < 1) {
			return '';
		}
		$name = '';
		foreach ($args as $key => $value) {
			$name .= $key . $this->_delemiter_key_value . $value . $this->_delemiter_base;
		}
		return mb_substr($name, 0, mb_strlen($name)-1);
	}

}
