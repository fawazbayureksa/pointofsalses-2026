<?php

namespace App\View\Components\Admin;

use Illuminate\View\Component;

class Modal extends Component
{
    public $id;

    public $title;

    public $size;

    public function __construct($id = 'modal', $title = null, $size = 'md')
    {
        $this->id = $id;
        $this->title = $title;
        $this->size = in_array($size, ['sm', 'md', 'lg', 'xl']) ? $size : 'md';
    }

    public function render()
    {
        return view('components.admin.modal');
    }
}
