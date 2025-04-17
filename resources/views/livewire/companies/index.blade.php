<div>
    <form wire:submit.prevent="save" enctype="multipart/form-data">
        @csrf

        <flux:field>
            <flux:input type="file" label="Companies Csv" wire:model="companiesCsv" />
        </flux:field>

        <flux:field class="my-2">
            <flux:button type="submit" class="w-fit !bg-blue-500" :loading="true">
                Save changes
            </flux:button>
        </flux:field>
    </form>
</div>