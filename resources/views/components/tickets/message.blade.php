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
@endphp

@if ($variant === 'reference')
    <article class="rounded-lg border border-[#c4c5d7] bg-[#f8f9ff] p-4">
        <header class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#dce9ff] text-xs font-bold text-[#0037b0]" aria-hidden="true">{{ $initial }}</span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-[#0b1c30]">{{ $authorName }}</p>
                    <p class="mt-0.5 text-xs text-[#434655]">{{ $authorLabel }}</p>
                </div>
            </div>

            @if ($createdAt)
                <time class="text-xs text-[#434655]" datetime="{{ $createdAt->toIso8601String() }}">{{ $createdAt->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }} WIB</time>
            @endif
        </header>

        <p class="mt-4 whitespace-pre-line text-sm leading-6 text-[#434655]">{{ $body }}</p>

        @if ($messageAttachments->isNotEmpty())
            <ul class="mt-4 flex flex-wrap gap-2 border-t border-[#c4c5d7] pt-3">
                @foreach ($messageAttachments as $attachment)
                    <li>
                        <a href="{{ $attachment['url'] }}" class="inline-flex max-w-full items-center gap-1.5 rounded-md border border-[#c4c5d7] bg-white px-2.5 py-1.5 text-xs font-bold text-[#0037b0] hover:border-[#0037b0]">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.44 11.05 12.25 20.24a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" /></svg>
                            <span class="truncate">{{ $attachment['name'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </article>
@else

<article class="rounded-2xl border p-4 sm:p-5 {{ $message->fromRequester ? 'border-[#dce7eb] bg-[#f8fbfc]' : 'border-[#cdeef7] bg-[#f5fcfe]' }}">
    <header class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-extrabold {{ $message->fromRequester ? 'bg-[#e3ecef] text-[#526f79]' : 'bg-[#d7f5fc] text-[#147a79]' }}" aria-hidden="true">{{ $message->initial() }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-extrabold text-[#35505b]">{{ $message->authorName }}</p>
                <p class="mt-0.5 text-xs text-[#78909a]">{{ $message->roleLabel() }}</p>
            </div>
        </div>

        @if ($message->createdAt)
            <time class="text-xs text-[#78909a]" datetime="{{ $message->createdAt->toIso8601String() }}">{{ $message->createdAt->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</time>
        @endif
    </header>

    <p class="mt-4 whitespace-pre-line text-sm leading-7 text-[#526f79]">{{ $message->body }}</p>

    @if ($message->hasAttachments())
        <ul class="mt-4 flex flex-wrap gap-2 border-t border-[#e3ecef] pt-3">
            @foreach ($message->attachments as $attachment)
                <li>
                    <a href="{{ $attachment['url'] }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#cfe0e5] bg-white px-2.5 py-1.5 text-xs font-bold text-[#0f766e] hover:border-[#98dff3]">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.44 11.05 12.25 20.24a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" /></svg>
                        {{ $attachment['name'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</article>
@endif
