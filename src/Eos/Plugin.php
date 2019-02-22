<?php

namespace xtype\Eos;

/**
 * Class Plugin
 * @package xtype\Eos
 * @property Client $client
 */
class Plugin
{
    // \xtype\Eos\Client
    protected $client = null;

    // Path
    protected $path = '';

    /**
     * Plugin constructor.
     * @param $path
     * @param $client
     */
    public function __construct($path, $client)
    {
        $this->path = $path;
        $this->client = $client;
    }

    /**
     * 调用方法
     * @param $method
     * @param $args
     * @return mixed|null
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        $params = [];
        if (isset($args[0]) && is_array($args[0])) {
            $params = $args[0];
        }
        $method = $this->toUnderScore($method);
        return $this->client->request("/{$this->path}/{$method}", $params);
    }

    /**
     * 驼峰转下划线
     * @param $str
     * @return string
     */
    private function toUnderScore($str)
    {
        $dstr = preg_replace_callback('/([A-Z]+)/', function($matchs) {
            return '_'.strtolower($matchs[0]);
        }, $str);
        return trim(preg_replace('/_{2,}/','_',$dstr),'_');
     }
}
