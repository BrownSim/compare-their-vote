<?php

namespace App\Datatable\Normalizer;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Twig\Environment;
use Twig\Error\Error;

class DatatableGenericNormalizer
{
    private readonly ?array $config;

    private readonly PropertyAccessorInterface $propertyAccessor;

    private readonly Environment $environment;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        Environment $environment,
        ?array $config = []
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->environment = $environment;
        $this->config = $config;
    }

    public function normalize(string $grid, PaginationInterface|array $resources): array
    {
        $dataRows = [];
        $gridDefinition = $this->config[$grid]['columns'] ?? [];
        $items = $resources instanceof PaginationInterface ? $resources->getItems() : $resources;
        $nbRecords = $resources instanceof PaginationInterface ? $resources->getTotalItemCount() : count($resources);

        foreach ($items as $resource) {
            $row = [];
            foreach ($gridDefinition as $key => $element) {
                try {
                    $path = $element['path'] ?? $key;
                    $template = $element['options']['template'] ?? null;
                    $data = '.' === $path ? $resource : $this->getValueFromResource($resource, $path);
                    $row[] = $template ? $this->environment->render($template, ['resource' => $data]) : $data;
                } catch (Error $e) {
                    $row[] = '';
                }
            }

            $dataRows[] = $row;
        }

        return [
            'data' => $dataRows,
            'recordsTotal' => $nbRecords,
            'recordsFiltered' => $nbRecords,
        ];
    }

    private function getValueFromResource(object|array $resource, string $path): mixed
    {
        if ('.' === $path) {
            return $resource;
        }

        if (is_object($resource)) {
            return $this->propertyAccessor->getValue($resource, $path);
        }

        if (strpos($path, '.') === false) {
            return $resource[$path];
        }

        $pathExploded = explode('.', $path, 2);

        if (empty($pathExploded)) {
            throw new \Error('Missing \'.\' from path');
        }

        return $this->getValueFromResource($resource[$pathExploded[0]], $pathExploded[1]);
    }
}
