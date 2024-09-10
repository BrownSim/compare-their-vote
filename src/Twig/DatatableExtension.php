<?php

namespace App\Twig;

use App\Datatable\Datatable;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DatatableExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('datatable_render_table', [$this, 'renderTable'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('datatable_render_columns', [$this, 'renderColumns'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    public function renderTable(Environment $env, Datatable $datatable, $params = [], ?string $template = null): string
    {
        return $env->render(
            $template ?: 'common/datatable/table.html.twig',
            ['datatable' => $datatable] + $params
        );
    }

    public function renderColumns(Environment $env, Datatable $datatable, ?string $template = null): string
    {
        return $env->render(
            $template ?: 'common/datatable/columns.html.twig',
            ['datatable' => $datatable]
        );
    }
}
