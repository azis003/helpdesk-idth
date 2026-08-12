@props(['message'])

<p {{ $attributes->merge(['class' => 'mt-2 flex items-start gap-1.5 text-sm font-semibold text-[color:var(--tm-danger-600)]']) }}>
    <svg class="mt-[0.15rem] h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-.75-11.75a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5ZM10 14.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
    </svg>
    <span class="min-w-0">{{ $message }}</span>
</p>
