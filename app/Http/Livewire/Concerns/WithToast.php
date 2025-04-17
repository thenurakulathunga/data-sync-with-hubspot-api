<?php

namespace App\Http\Livewire\Concerns;

use Illuminate\Foundation\Events\Dispatchable;

trait WithToast
{
    use Dispatchable;

    public function toast(string $message, string $type = 'success', int $duration = 5000): void
    {
        $this->dispatch('showToast', [
            'message' => $message,
            'type' => $type,
            'duration' => $duration,
        ]);
    }

    public function toastSuccess(string $message, int $duration = 5000): void
    {

        $this->toast($message, 'success', $duration);
    }

    public function toastError(string $message, int $duration = 5000): void
    {
        $this->toast($message, 'error', $duration);
    }

    public function toastWarning(string $message, int $duration = 5000): void
    {
        $this->toast($message, 'warning', $duration);
    }

    public function toastInfo(string $message, int $duration = 5000): void
    {
        $this->toast($message, 'info', $duration);
    }
}
