<!-- FileUpload.blade.php -->
@props(['accept' => '*', 'disabled' => false])

<input {{ $attributes->merge(['type' => 'file', 'id' => 'fileInput', 'accept' => $accept, 'disabled' => $disabled,
'class' => 'w-full max-w-md text-sm text-on-surface-strong dark:text-on-surface-dark-strong
bg-surface-alt/50 dark:bg-surface-dark-alt/50
rounded-lg border border-surface-alt dark:border-surface-dark-alt
file:mr-4 file:border-0 file:bg-surface-alt dark:file:bg-surface-dark-alt
file:px-4 file:py-2 file:text-sm file:font-medium file:text-on-surface-strong dark:file:text-on-surface-dark-strong
hover:file:bg-surface-alt-hover dark:hover:file:bg-surface-dark-alt-hover
focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-primary-dark
focus:border-primary dark:focus:border-primary-dark
transition-colors duration-200 ease-in-out
disabled:cursor-not-allowed disabled:opacity-75'])
}}
/>
