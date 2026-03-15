<?php

namespace App\View\Components\Admin;

use Illuminate\View\Component;

class FormInput extends Component
{
    public $name;

    public $label;

    public $type;

    public $value;

    public $placeholder;

    public $required;

    public $disabled;

    public $readonly;

    public $error;

    public $help;

    public $options;

    public $multiple;

    public function __construct(
        $name = null,
        $label = null,
        $type = 'text',
        $value = null,
        $placeholder = null,
        $required = false,
        $disabled = false,
        $readonly = false,
        $error = null,
        $help = null,
        $options = [],
        $multiple = false
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->type = in_array($type, ['text', 'email', 'password', 'number', 'textarea', 'select', 'checkbox', 'file']) ? $type : 'text';
        $this->value = $value;
        $this->placeholder = $placeholder;
        $this->required = $required;
        $this->disabled = $disabled;
        $this->readonly = $readonly;
        $this->error = $error;
        $this->help = $help;
        $this->options = $options;
        $this->multiple = $multiple;
    }

    public function render()
    {
        return view('components.admin.form-input');
    }
}
