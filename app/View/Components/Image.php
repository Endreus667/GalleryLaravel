<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Image extends Component
{

    public $url;
    public $id;
    public $title;

    public function __construct($url, $title, $id = null) {
        $this->url = $url;
        $this->title = $title;
        $this->id = $id;
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.image');
    }
}
