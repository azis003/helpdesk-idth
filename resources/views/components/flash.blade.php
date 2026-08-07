@if (session('success') || session('warning') || $errors->any())
    <div data-toast-stack class="pointer-events-none fixed right-4 top-20 z-[80] flex w-[calc(100vw-2rem)] max-w-md flex-col gap-3 sm:right-6 sm:top-24" aria-label="Notifikasi">
        @if (session('success'))
            <div data-toast data-toast-duration="5500" role="status" class="pointer-events-auto relative flex items-start gap-3 overflow-hidden rounded-2xl border border-emerald-200 bg-[#f4fffb]/95 px-4 py-3.5 text-sm text-emerald-900 shadow-[0_18px_45px_rgba(33,57,67,0.16)] ring-1 ring-white/80 backdrop-blur-sm transition duration-300 ease-out">
                <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white" aria-hidden="true">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                </span>
                <p class="min-w-0 flex-1 pt-0.5 leading-6">{{ session('success') }}</p>
                <button type="button" data-toast-close class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-emerald-700/70 transition hover:bg-emerald-100 hover:text-emerald-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" aria-label="Tutup notifikasi">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                </button>
            </div>
        @endif

        @if (session('warning'))
            <div data-toast data-toast-duration="6500" role="status" class="pointer-events-auto relative flex items-start gap-3 overflow-hidden rounded-2xl border border-amber-200 bg-[#fffaf0]/95 px-4 py-3.5 text-sm text-amber-950 shadow-[0_18px_45px_rgba(33,57,67,0.16)] ring-1 ring-white/80 backdrop-blur-sm transition duration-300 ease-out">
                <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-500 font-extrabold text-white" aria-hidden="true">!</span>
                <p class="min-w-0 flex-1 pt-0.5 leading-6">{{ session('warning') }}</p>
                <button type="button" data-toast-close class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-amber-700/70 transition hover:bg-amber-100 hover:text-amber-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500" aria-label="Tutup notifikasi">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div data-toast data-toast-duration="0" role="alert" class="pointer-events-auto relative flex items-start gap-3 overflow-hidden rounded-2xl border border-rose-200 bg-[#fff6f7]/95 px-4 py-3.5 text-sm text-rose-950 shadow-[0_18px_45px_rgba(33,57,67,0.16)] ring-1 ring-white/80 backdrop-blur-sm transition duration-300 ease-out">
                <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-500 font-extrabold text-white" aria-hidden="true">!</span>
                <div class="min-w-0 flex-1">
                    <p class="font-bold leading-6">Periksa kembali data yang dimasukkan.</p>
                    <ul class="mt-1.5 max-h-44 list-disc space-y-1 overflow-y-auto pl-5 text-sm leading-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" data-toast-close class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-rose-700/70 transition hover:bg-rose-100 hover:text-rose-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500" aria-label="Tutup notifikasi">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                </button>
            </div>
        @endif
    </div>
@endif
