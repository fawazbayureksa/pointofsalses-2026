<?php

namespace App\View\Components\Admin;

use Illuminate\View\Component;

class Button extends Component
{
    public $type;

    public $variant;

    public $size;

    public $icon;

    public $loading;

    public $disabled;

    public $href;

    public function __construct(
        $type = 'button',
        $variant = 'primary',
        $size = 'md',
        $icon = null,
        $loading = false,
        $disabled = false,
        $href = null
    ) {
        $this->type = in_array($type, ['button', 'submit']) ? $type : 'button';
        $this->variant = in_array($variant, ['primary', 'secondary', 'success', 'danger', 'warning', 'ghost']) ? $variant : 'primary';
        $this->size = in_array($size, ['sm', 'md', 'lg']) ? $size : 'md';
        $this->icon = $icon;
        $this->loading = $loading;
        $this->disabled = $disabled || $loading;
        $this->href = $href;
    }

    public function render()
    {
        return view('components.admin.button');
    }
}
