<div>
    <button wire:click="initiatePayment" wire:loading.attr="disabled" wire:loading.class="opacity-50"
        class="px-2 py-1 text-xs font-medium text-blue-500 bg-indigo-100 rounded-full dark:text-blue-400 dark:bg-blue-500/10 disabled:opacity-50">
        <span wire:loading.remove>Renew</span>
        <span wire:loading>Processing...</span>
    </button>

    <livewire:pages.user.subscription.payment-method-selection>
</div>