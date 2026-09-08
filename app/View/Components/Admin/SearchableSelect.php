<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SearchableSelect extends Component
{
    /**
     * @param  array<int, array{value: string|int, text: string, search: string}>  $options
     */
    public function __construct(
        public string $target,
        public array $options = [],
        public string|int|null $selected = null,
        public string $placeholder = 'Select...',
        public string $searchPlaceholder = 'Search...',
        public ?string $clearEvent = null,
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.admin.searchable-select');
    }
}
