<?php

namespace App\Datatable;

class Datatable
{
    private readonly array $config;

    private array $columns = [];

    private array $sortableColumns = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $columns = $this->config['columns'] ?? [];

        foreach ($columns as $key => $data) {
            $col = new Column();
            $col->name = $key;
            $col->label = $data['label'] ?? $key;

            $this->columns[$key] = $col;

            if (array_key_exists('sortable', $data)) {
                $this->sortableColumns[$key]['order'] = $data['sortable'] ?? true;
            }
        }
    }

    public function hasHeader(): bool
    {
        return $this->config['settings']['header'] ?? true;
    }

    public function getNbElement(): int
    {
        return $this->config['settings']['nb_element'] ?? 20;
    }

    public function getSortableColumns(): array
    {
        return $this->sortableColumns;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }
}
