<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        // Arahkan ke file layout yang sudah kita buat di resources/views/layouts/
        return view('layouts.admin'); 
    }
}