<?php

namespace xtype\Eos;

use GuzzleHttp\Client as GuzzleHttp;

class Client
{
    // instence
    public static $instence = null;

    // GuzzleHttp
    protected $client = null;

    // 私钥列表
    protected $priKeys = [];

    // Plugin version
    public $version = 'v1';

    /**
     *
     */
    public function __construct($options = [])
    {
        $defaultOptions = [
            'base_uri' => 'http://127.0.0.1:8888',
            'timeout' => 10,
            'verify' => false,
        ];
        if (is_string($options)) {
            $this->client = new GuzzleHttp(array_merge($defaultOptions, ['base_uri' => $options]));
        } else {
            $this->client = new GuzzleHttp(array_merge($defaultOptions, $options));
        }
    }

    /**
     * 获取一个默认单列
     */
    public static function instance()
    {
        if (self::$instence == null) {
            self::$instence = new static(config('common.eos.host'));
        }
        return self::$instence;
    }

    /**
     * 设置私钥
     */
    public function addPrivateKeys(array $priKeys)
    {
        foreach ($priKeys as $key => $value) {
            if (!Ecc::isValidPrivate($value)) {
                throw new \Exception("$value Is Error Wif Private Key", 1);
            }
        }
        $this->priKeys = array_unique(array_merge($this->priKeys, $priKeys));
    }

    /**
     * 移除私钥
     */
    public function removePrivateKeys(array $priKeys)
    {
        $this->priKeys = array_unique(array_diff($this->priKeys, $priKeys));
    }

    /**
     * 交易
     */
    public function transtion(array $transtion, $blocksBehind = 3, $expireSeconds = 30)
    {
        // 序列化 Action Data
        foreach ($transtion['actions'] as $key => $value) {
            $transtion['actions'][$key]['data'] = $this->chain()->abiJsonToBin([
                'code' => $value['account'],
                'action' => $value['name'],
                'args' => $value['data'],
            ])->binargs;
        }

        // 获取区块信息
        $info = $this->chain()->getInfo();
        $block = $this->chain()->getBlock([
            'block_num_or_id' => $info->head_block_num - $blocksBehind
        ]);

        // 设置交易过期时间，UTC时区
        $default = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $expiration = strtotime($info->head_block_time) + $expireSeconds;
        date_default_timezone_set($default);

        // 合并数据
        $transtion = array_merge([
            'expiration' => $expiration,
            'ref_block_num' => $block->block_num & 0xffff,
            'ref_block_prefix' => $block->ref_block_prefix,
            'max_net_usage_words' => 0,
            'max_cpu_usage_ms' => 0,
            'delay_sec' => 0,
            'context_free_actions' => [],
            'actions' => [],
            'transaction_extensions' => [],
        ], $transtion);

        // 序列化交易
        $st = $this->serializeTransaction($transtion);
        $chainId = $info->chain_id;
        // 签名：
        $signatures = $this->signTranstion($chainId, $st);

        return $this->chain()->pushTransaction([
            'signatures' => $signatures,
            'compression' => 0,
            'packed_context_free_data' => '',
            'packed_trx' => $st,
        ]);
    }

    /**
     * 序列化交易
     * @return 返回序列化交易的十六进制
     */
    public function serializeTransaction(array $transtion)
    {
        return Serialize::transtion($transtion);
    }

    /**
     * 事物签名
     * @return Array
     */
    public function signTranstion(string $chainId, $stranstion)
    {
        $packedContextFreeData = '0000000000000000000000000000000000000000000000000000000000000000';
        $signBuf = $chainId . $stranstion . $packedContextFreeData;

        $signatures = [];
        foreach ($this->priKeys as $key => $value) {
            $signatures[] = Ecc::sign($signBuf, $value);
        }

        return $signatures;
    }

    /**
     * Plugin chain rpc
     */
    public function chain()
    {
        return new Plugin("{$this->version}/chain", $this);
    }

    /**
     * Plugin history rpc
     */
    public function history()
    {
        return new Plugin("{$this->version}/history", $this);
    }

    /**
     * Plugin net rpc
     */
    public function net()
    {
        return new Plugin("{$this->version}/net", $this);
    }

    /**
     * Plugin db_size rpc
     */
    public function dbSize()
    {
        return new Plugin("{$this->version}/db_size", $this);
    }

    /**
     * Plugin db_size rpc
     */
    public function db_size()
    {
        return $this->dbSize();
    }

    /**
     * Plugin wallet rpc
     */
    public function wallet()
    {
        return new Plugin("{$this->version}/wallet", $this);
    }

    /**
     * 发出请求
     */
    public function request($path, $params = [])
    {
        $data = [];
        if ($params) {
            $data['json'] = $params;
        }

        try {
            $res = $this->client->post($path, $data);
            return json_decode($res->getBody());
        } catch(\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $body = json_decode($e->getResponse()->getBody());
                // var_dump($body);
                throw new \Exception(json_encode($body->error->details), $body->code);
            }
        }

        return null;
    }
}
