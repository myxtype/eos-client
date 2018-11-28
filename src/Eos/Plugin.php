<?php

namespace xtype\Eos;

/**
 * 插件基础类
 */
class Plugin
{
    // Eos
    protected $client = null;

    // name
    protected $path = '';

    /**
     * @param $name string
     * @param $client \xtype\Eos\Client
     */
    public function __construct($path, $client)
    {
        $this->path = $path;
        $this->client = $client;
    }

    /**
     * 调用方法
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
     */
    private function toUnderScore($str)
    {
        $dstr = preg_replace_callback('/([A-Z]+)/', function($matchs) {
            return '_'.strtolower($matchs[0]);
        }, $str);
        return trim(preg_replace('/_{2,}/','_',$dstr),'_');
     }
}
