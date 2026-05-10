<?php

namespace App\Livewire;

use App\Models\AwardWinner;
use App\Models\Edition;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Winners extends Component
{
    #[Layout('components.layouts.public')]
    public function render()
    {
        $edition = Edition::active();
        $winners = AwardWinner::forEditionKeyed($edition?->id);

        return view('livewire.winners', [
            'edition' => $edition,
            'winners' => $winners,
        ]);
    }
}
