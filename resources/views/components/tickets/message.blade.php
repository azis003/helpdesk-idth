@props(['message', 'variant' => 'default', 'requesterId' => null])

@php
    $isOperationalMessage = $message instanceof \App\Models\TicketComment;
    $authorName = $isOperationalMessage ? ($message->author?->name ?? 'Sistem') : $message->authorName;
    $authorLabel = $isOperationalMessage
        ? ($message->visibility === \App\Enums\TicketCommentVisibility::Internal
            ? 'Catatan Internal'
            : ((int) $message->author_id === (int) $requesterId ? 'Pemohon' : 'Tim TI'))
        : $message->roleLabel();
    $initial = $isOperationalMessage
        ? strtoupper(mb_substr($authorName, 0, 1))
        : $message->initial();
    $createdAt = $isOperationalMessage ? $message->created_at : $message->createdAt;
    $body = $isOperationalMessage ? $message->body : $message->body;
    $messageAttachments = $isOperationalMessage
        ? $message->attachments->map(fn ($attachment): array => [
            'name' => $attachment->original_name,
            'url' => route('attachments.download', $attachment),
        ])
        : collect($message->attachments);
    $isInternalNote = $isOperationalMessage
        && $message->visibility === \App\Enums\TicketCommentVisibility::Internal;
@endphp

@if ($variant === 'reference')
    <article class="rounded-[var(--tm-r-md)] border p-4 transition-shadow duration-200 hover:shadow-[var(--tm-sh-sm)] {{ $isInternalNote ? 'border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)]' : 'border-[color:var(--tm-border)] bg-[color:var(--tm-n-25)]' }}">
        <header class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $isInternalNote ? 'bg-[color:var(--tm-warning-100)] text-[color:var(--tm-warning-700)]' : 'bg-[color:var(--tm-brand-100)] text-[color:var(--tm-brand-700)]' }}" aria-hidden="true">{{ $initial }}</span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-[color:var(--tm-text)]">{{ $authorName }}</p>
                    <p class="mt-0.5 text-xs {{ $isInternalNote ? 'font-bold text-[color:var(--tm-warning-700)]' : 'text-[color:var(--tm-text-muted)]' }}">{{ $authorLabel }}</p>
                </div>
            </div>

            @if ($createdAt)
                <time class="text-xs tabular-nums text-[color:var(--tm-text-muted)]" datetime="{{ $createdAt->toIso8601String() }}">{{ $createdAt->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }} WIB</time>
            @endif
        </header>

        <p class="mt-4 whitespace-pre-line text-sm leading-6 text-[color:var(--tm-text-secondary)]">{{ $body }}</p>

        @if ($messageAttachments->isNotEmpty())
            <ul class="mt-4 flex flex-wrap gap-2 border-t border-[color:var(--tm-border-subtle)] pt-3">
                @foreach ($messageAttachments as $attachment)
                    <li>
                        <a href="{{ $attachment['url'] }}" class="inline-flex max-w-full items-center gap-1.5 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] px-2.5 py-1.5 text-xs font-bold text-[color:var(--tm-brand-700)] transition hover:border-[color:var(--tm-brand-400)] hover:bg-[color:var(--tm-brand-50)]">
                            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.44 11.05 12.25 20.24a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" /></svg>
                            <span class="truncate">{{ $attachment['name'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </article>
@else

<article class="rounded-[var(--tm-r-lg)] border p-4 transition-shadow duration-200 hover:shadow-[var(--tm-sh-sm)] sm:p-5 {{ $message->fromRequester ? 'border-[color:var(--tm-border)] bg-[color:var(--tm-n-25)]' : 'border-[color:var(--tm-brand-100)] bg-[color:var(--tm-brand-50)]' }}">
    <header class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-md)] text-xs font-extrabold {{ $message->fromRequester ? 'bg-[color:var(--tm-n-100)] text-[color:var(--tm-text-secondary)]' : 'bg-[color:var(--tm-brand-100)] text-[color:var(--tm-brand-700)]' }}" aria-hidden="true">{{ $message->initial() }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-extrabold text-[color:var(--tm-text)]">{{ $message->authorName }}</p>
                <p class="mt-0.5 text-xs text-[color:var(--tm-text-muted)]">{{ $message->roleLabel() }}</p>
            </div>
        </div>

        @if ($message->createdAt)
            <time class="text-xs tabular-nums text-[color:var(--tm-text-muted)]" datetime="{{ $message->createdAt->toIso8601String() }}">{{ $message->createdAt->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</time>
        @endif
    </header>

    <p class="mt-4 whitespace-pre-line text-sm leading-7 text-[color:var(--tm-text-secondary)]">{{ $message->body }}</p>

    @if ($message->hasAttachments())
        <ul class="mt-4 flex flex-wrap gap-2 border-t border-[color:var(--tm-border-subtle)] pt-3">
            @foreach ($message->attachments as $attachment)
                <li>
                    <a href="{{ $attachment['url'] }}" class="inline-flex items-center gap-1.5 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] px-2.5 py-1.5 text-xs font-bold text-[color:var(--tm-brand-700)] transition hover:border-[color:var(--tm-brand-400)] hover:bg-[color:var(--tm-brand-50)]">
                        <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.44 11.05 12.25 20.24a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" /></svg>
                        {{ $attachment['name'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</article>
@endif
