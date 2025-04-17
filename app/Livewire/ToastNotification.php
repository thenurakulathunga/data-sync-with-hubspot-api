<?php

namespace App\Livewire;

// use Illuminate\Foundation\Events\Dispatchable;
use Livewire\Attributes\On;
use Livewire\Component;

class ToastNotification extends Component
{
    public string $message = '';

    public string $type = 'success';

    public bool $show = false;

    public int $duration = 5000;

    #[On('showToast')]
    public function showToast(array $data): void
    {
        $this->message = $data['message'];
        $this->type = $data['type'] ?? 'success';
        $this->duration = $data['duration'] ?? 5000;
        $this->show = true;

        // dump($this->message, $this->type, $this->duration , $this->show);
        $this->dispatch('start-toast-timer', ['duration' => $this->duration]);
    }

    public function hide(): void
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.toast-notification');
    }
}
