<?php

namespace App\Datatable;

class DatatablePool
{
    private array $configs;

    public function __construct(?array $configs = [])
    {
        $this->configs = $configs;
    }

    public function getConfigurationByCode(string $code)
    {
        if (!isset($this->configs[$code])) {
            throw new \Exception(sprintf('No datatable configuration found for code "%s"', $code));
        }

        return $this->configs[$code];
    }
}
