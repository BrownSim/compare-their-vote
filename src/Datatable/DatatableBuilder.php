<?php

namespace App\Datatable;

class DatatableBuilder
{
    private readonly DatatablePool $datatablePool;

    public function __construct(DatatablePool $datatablePool)
    {
        $this->datatablePool = $datatablePool;
    }

    public function build($code): Datatable
    {
        $config = $this->datatablePool->getConfigurationByCode($code);

        return new Datatable($config);
    }
}
