<?php

namespace LikeLight;

class Config
{
    protected $configFile;
    protected $values;
    protected $controller;

    /**
     * @param Controller $controller
     * @param null $path
     * @throws \Exception
     */
    public function __construct(Controller $controller, $path = null)
    {
        $this->controller = $controller;

        if ($path === null) {
            $this->configFile = dirname(__DIR__) . '/config.json';
        } else {
            $this->configFile = $path;
        }
        if (!is_writable($this->configFile) || !is_readable($this->configFile)) {
            throw new \Exception("The config file '{$this->configFile}' must be readable and writable");
        }

        $this->values = json_decode(file_get_contents($this->configFile));
        if ($this->values === null || $this->values === false) {
            throw new \Exception("The config file '{$this->configFile}' may not be valid json");
        }
    }

    /**
     * @param $value
     * @param null $default
     * @return mixed
     */
    public function get($value, $default = null)
    {
        if (isset($this->values->{$value})) {
            return $this->values->{$value};
        }
        return $default;
    }

    /**
     * @param $short
     * @param $long
     */
    public function setTokens($short, $long)
    {
        $this->values->fb_token->short = $short;
        $this->values->fb_token->long = $long;
        file_put_contents($this->configFile, json_encode($this->values));
    }
}
