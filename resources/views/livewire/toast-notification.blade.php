<div>
    <div x-data="{ show: $wire.entangle('show') }" x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2" class="fixed bottom-4 right-4 z-50 w-80 max-w-full" x-cloak>
        <div
            class="rounded-lg shadow-lg overflow-hidden border 
            @if ($type === 'success') border-green-200 bg-green-100
            @elseif($type === 'error') border-red-200 bg-red-100
            @elseif($type === 'warning') border-yellow-200 bg-yellow-100
            @else border-blue-200 bg-blue-100 @endif
        ">
            <div class="p-4 text-sm text-gray-800">
                {{ $message }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('start-toast-timer', ({
                duration = 5000
            }) => {
                console.log(duration)
                setTimeout(() => {
                    @this.hide();
                }, duration);
            });
        });
    </script>
</div>
