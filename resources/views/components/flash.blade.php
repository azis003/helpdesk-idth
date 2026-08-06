@if (session('success'))
    <div role="status" class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-[#effcf6] px-4 py-3.5 text-sm text-emerald-900 shadow-sm">
        <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white" aria-hidden="true">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
        </span>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('warning'))
    <div role="status" class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-[#fff9e9] px-4 py-3.5 text-sm text-amber-900 shadow-sm">
        <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white" aria-hidden="true">!</span>
        <span>{{ session('warning') }}</span>
    </div>
@endif

@if ($errors->any())
    <div role="alert" class="mb-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-[#fff2f3] px-4 py-3.5 text-sm text-rose-900 shadow-sm">
        <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-500 font-bold text-white" aria-hidden="true">!</span>
        <div>
            <p class="font-bold">Periksa kembali data yang dimasukkan.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    </div>
@endif
