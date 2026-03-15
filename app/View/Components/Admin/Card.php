<?php

namespace App\View\Components\Admin;

use Illuminate\View\Component;

class Card extends Component
{
    public $title;

    public $actions;

    public $collapsible;

    public $collapsed;

    public function __construct($title = null, $actions = null, $collapsible = false, $collapsed = false)
    {
        $this->title = $title;
        $this->actions = $actions;
        $this->collapsible = $collapsible;
        $this->collapsed = $collapsed;
    }

    public function render()
    {
        return view('components.admin.card');
    }
}
