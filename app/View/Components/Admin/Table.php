<?php

namespace App\View\Components\Admin;

use Illuminate\View\Component;

class Table extends Component
{
    public $headers;

    public $rows;

    public $actions;

    public $empty;

    public $sortable;

    public $sortColumn;

    public $sortDirection;

    public function __construct($headers = [], $rows = [], $actions = null, $empty = 'No data available', $sortable = false, $sortColumn = null, $sortDirection = 'asc')
    {
        $this->headers = $headers;
        $this->rows = $rows;
        $this->actions = $actions;
        $this->empty = $empty;
        $this->sortable = $sortable;
        $this->sortColumn = $sortColumn;
        $this->sortDirection = $sortDirection;
    }

    public function render()
    {
        return view('components.admin.table');
    }
}
