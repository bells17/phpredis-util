<?php
/**
 * RedisConnectionCollecter
 * Redisへのコネクション管理クラス
 * use phpredis
 * https://github.com/nicolasff/phpredis
 * @author hayakawa
 */
class RedisConnectionCollecter {

    const HOST         = '127.0.0.1';
    const PORT         = 6379;
    const PASSEORD     = null;

    private static $_connections    = array();
    private static $_info           = array();

    /**
     * DB情報を設定します
     * 初めにこれで設定してから使うこと
     *
     * array(
     *         "keyname" => array(
     *                            'dbname'   => [dbname]<データベース名>  required,
     *                            'host'     => [hostname]<ホスト名>     default:   self::HOST,
     *                            'port'     => [port]<ポート番号>       default:   self::PORT,
     *                            'password' => [password]<パスワード>   default:   self::PASSEORD
     *                    ),
     *          ~
     * );
     */
    public static function setDbInfo(array $dbs)
    {
        foreach ($dbs as $key => $info) {
            self::$_info[$key] = self::checkDbInfoArray($info);
        }
    }

    /**
     * DB情報用の配列の値をチェックして、ないものにはデフォルト値を設定します(dbnameは必須)
     */
    private static function checkDbInfoArray(array $info)
    {
        if (isset($info['dbname']) === false) {
            throw new Exception("Not exist database name!");
        }
        if (isset($info['host']) === false) {
            $info['host'] = self::HOST;
        }
        if (isset($info['port']) === false) {
            $info['port'] = self::PORT;
        }
        if (isset($info['password']) === false) {
            $info['password'] = self::PASSEORD;
        }
        return $info;
    }


    /**
     * 指定したキーの接続済みredisインスタンスを返します
     * 接続済みredisインスタンスの取得はこのメソッドを使用する
     */
    public static function getConnection($key)
    {
        if(isset(self::$_connections[$key])) {
            return $_connections[$key];
        }
        return self::connect($key);
    }

    /**
     * DB接続済みのRedisインスタンスを返します
     */
    private static function connect($key)
    {
        if (isset(self::$_info[$key]) === false) {
            throw new Exception("Not Exist db key {$key}");
        }

        $info = self::$_info[$key];
        $redis = new redis();
        // if (!$redis->connect($info['host'], $info['port'])) {
        //     throw new Exception("failed to connect server!");
        // }
        // 持続的接続に変更
        if (!$redis->pconnect($info['host'], $info['port'])) {
            throw new Exception("failed to connect server!");
        }

        if ($info['password'] !== self::PASSEORD) {
            if(!$redis->auth($info['password'])) {
                throw new Exception("failed to auth!");
            }
        }

        $redis->select($info['dbname']);
        return self::$_connections[$key] = $redis;
    }

    /**
     * コネクションを閉じる場合はこちらを使う
     */
    public static function close($key)
    {
        if(isset(self::$_info[$key])
            && isset(self::$_connections[$key])) {
            $_connections[$key]->close();
            unset($_connections[$key]);
        }
    }

}