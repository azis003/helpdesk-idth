@extends('layouts.app')

@php
    $autoOpenForm = old('_skill_form');

    if (! $autoOpenForm && old('_skill_create')) {
        $autoOpenForm = 'create';
    }

    if (! $autoOpenForm && old('_skill_edit')) {
        $autoOpenForm = 'edit-'.old('_skill_edit');
    }
@endphp

@section('title', 'Keahlian — '.$branding['application_name'])
@section('header_kicker', 'Manajemen Keahlian')
@section('header_title', 'Keahlian')

@section('content')
    <x-page-header
        eyebrow="Data Master · Kapabilitas"
        title="Manajemen Keahlian"
        description="Kelola bidang keahlian dan pemetaannya untuk membantu triase serta penugasan tiket."
    />

    <section class="mt-7 overflow-hidden rounded-lg border border-[#d7dde0] bg-white shadow-[0_2px_8px_rgba(36,57,67,0.06)]" aria-labelledby="skills-heading">
        <div class="flex flex-col gap-4 bg-[#075998] px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div>
                <h2 id="skills-heading" class="text-xl font-extrabold tracking-tight">Daftar Keahlian</h2>
                <p class="mt-1 text-xs leading-5 text-blue-100">Gunakan pemetaan ini untuk membantu triase dan penugasan.</p>
            </div>
            <button type="button" data-ui-modal-open="skill-create-modal" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-[#7138e8] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#6229d5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#075998]" aria-label="Tambah keahlian">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Tambah
            </button>
        </div>

        <div class="px-5 py-5 sm:px-8">
            <form method="GET" action="{{ route('admin.skills.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 text-sm text-[#17212b]">
                    <label for="skill-per-page" class="font-bold">Tampilkan</label>
                    <select id="skill-per-page" name="per_page" class="h-10 rounded-lg border border-[#d7e0e4] bg-white px-3 text-sm text-[#35505b] outline-none focus:border-[#0a87c9] focus:ring-2 focus:ring-[#0a87c9]/15" onchange="this.form.submit()">
                        @foreach ([10, 25, 50] as $pageSize)
                            <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                        @endforeach
                    </select>
                    <span>data</span>
                </div>

                <div class="flex w-full items-center gap-2 sm:w-auto">
                    <label for="skill-search" class="shrink-0 text-sm font-bold text-[#17212b]">Cari:</label>
                    <input id="skill-search" name="q" type="search" value="{{ $search }}" class="h-10 w-full min-w-0 rounded-lg border border-[#d7e0e4] bg-[#f8fafb] px-3 text-sm text-[#17212b] outline-none placeholder:text-[#9baab0] focus:border-[#0a87c9] focus:bg-white focus:ring-2 focus:ring-[#0a87c9]/15 sm:w-56" placeholder="Cari keahlian" aria-label="Cari keahlian">
                    <button type="submit" class="sr-only">Cari keahlian</button>
                </div>
            </form>

            <div class="mt-4 hidden overflow-x-auto rounded-lg border border-[#cfd6da] md:block">
                <table class="min-w-[780px] w-full border-collapse text-left text-sm">
                    <caption class="sr-only">Daftar keahlian beserta layanan terpetakan, status, dan aksi</caption>
                    <thead class="bg-[#fbfcfd] text-[#34495a]">
                        <tr>
                            <th scope="col" class="w-16 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">No</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Nama Keahlian</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Layanan Terpetakan</th>
                            <th scope="col" class="w-28 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">Status</th>
                            <th scope="col" class="w-40 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($skills as $skill)
                            <tr class="odd:bg-[#f8fafb] even:bg-white hover:bg-[#eef7fc]">
                                <td class="border-b border-[#e5eaed] px-4 py-5 text-center font-semibold text-[#172d45]">{{ ($skills->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="border-b border-[#e5eaed] px-4 py-5">
                                    <p class="font-semibold text-[#112b49]">{{ $skill->name }}</p>
                                    @if ($skill->description)
                                        <p class="mt-1 max-w-[24rem] truncate text-xs text-[#78909a]">{{ $skill->description }}</p>
                                    @endif
                                </td>
                                <td class="border-b border-[#e5eaed] px-4 py-5">
                                    @if ($skill->serviceTypes->isNotEmpty())
                                        <ul class="list-disc space-y-1 pl-4 text-[#172d45]">
                                            @foreach ($skill->serviceTypes as $serviceType)
                                                <li>{{ $serviceType->code }} · {{ $serviceType->name }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-[#78909a]">Belum ada layanan</span>
                                    @endif
                                </td>
                                <td class="border-b border-[#e5eaed] px-4 py-5 text-center">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $skill->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">
                                        {{ $skill->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="border-b border-[#e5eaed] px-4 py-5">
                                    <div class="flex justify-center gap-2">
                                        <button type="button" data-ui-modal-open="skill-edit-modal-{{ $skill->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#f1b900] text-white transition hover:bg-[#d49f00] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f1b900] focus-visible:ring-offset-2" aria-label="Edit keahlian {{ $skill->name }}" title="Edit keahlian">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                        </button>
                                        @if ($skill->is_active)
                                            <form method="POST" action="{{ route('admin.skills.deactivate', $skill) }}" data-swal-confirm="Nonaktifkan keahlian {{ $skill->name }}? Pemetaan dan histori yang ada tetap disimpan." class="flex">
                                                @csrf
                                                <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#6d46db] text-white transition hover:bg-[#5b35c6] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6d46db] focus-visible:ring-offset-2" aria-label="Nonaktifkan keahlian {{ $skill->name }}" title="Nonaktifkan keahlian">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M12 4v8M7.2 6.4a7 7 0 1 0 9.6 0" /></svg>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.skills.activate', $skill) }}" class="flex">
                                                @csrf
                                                <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#0b98e5] text-white transition hover:bg-[#087fc1] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0b98e5] focus-visible:ring-offset-2" aria-label="Aktifkan keahlian {{ $skill->name }}" title="Aktifkan keahlian">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" data-swal-confirm="Hapus keahlian {{ $skill->name }} secara lunak? Histori pemetaan tetap tersedia." class="flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#e94f70] text-white transition hover:bg-[#d63d5e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e94f70] focus-visible:ring-offset-2" aria-label="Hapus keahlian {{ $skill->name }}" title="Hapus keahlian">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-[#718088]">
                                    {{ $search !== '' ? 'Tidak ada keahlian yang cocok dengan pencarian.' : 'Belum ada keahlian yang terdaftar.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-col gap-3 text-sm text-[#718088] sm:flex-row sm:items-center sm:justify-between">
                <p>
                    @if ($skills->total() > 0)
                        Menampilkan {{ $skills->firstItem() }}–{{ $skills->lastItem() }} dari {{ $skills->total() }} keahlian
                    @else
                        Tidak ada data keahlian
                    @endif
                </p>
                @if ($skills->hasPages())
                    <div>{{ $skills->links() }}</div>
                @endif
            </div>
        </div>

        <div class="divide-y divide-[#e5eaed] md:hidden">
            @forelse ($skills as $skill)
                <article class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-[#78909a]">No. {{ ($skills->firstItem() ?? 1) + $loop->index }}</p>
                            <h3 class="mt-1 font-bold text-[#112b49]">{{ $skill->name }}</h3>
                            @if ($skill->description)
                                <p class="mt-1 text-xs leading-5 text-[#78909a]">{{ $skill->description }}</p>
                            @endif
                        </div>
                        <span class="rounded-full px-2 py-1 text-[0.65rem] font-bold {{ $skill->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">{{ $skill->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <dl class="mt-4 rounded-lg bg-[#f8fafb] p-4 text-sm">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Layanan Terpetakan</dt>
                            <dd class="mt-1 text-[#172d45]">
                                @if ($skill->serviceTypes->isNotEmpty())
                                    {{ $skill->serviceTypes->map(fn ($serviceType) => $serviceType->code.' · '.$serviceType->name)->join(', ') }}
                                @else
                                    <span class="text-[#78909a]">Belum ada layanan</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" data-ui-modal-open="skill-edit-modal-{{ $skill->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#f1b900] text-white transition hover:bg-[#d49f00] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f1b900] focus-visible:ring-offset-2" aria-label="Edit keahlian {{ $skill->name }}" title="Edit keahlian">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                        </button>
                        @if ($skill->is_active)
                            <form method="POST" action="{{ route('admin.skills.deactivate', $skill) }}" data-swal-confirm="Nonaktifkan keahlian {{ $skill->name }}? Pemetaan dan histori yang ada tetap disimpan." class="flex">
                                @csrf
                                <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#6d46db] text-white transition hover:bg-[#5b35c6] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6d46db] focus-visible:ring-offset-2" aria-label="Nonaktifkan keahlian {{ $skill->name }}" title="Nonaktifkan keahlian">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M12 4v8M7.2 6.4a7 7 0 1 0 9.6 0" /></svg>
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.skills.activate', $skill) }}" class="flex">
                                @csrf
                                <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#0b98e5] text-white transition hover:bg-[#087fc1] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0b98e5] focus-visible:ring-offset-2" aria-label="Aktifkan keahlian {{ $skill->name }}" title="Aktifkan keahlian">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" data-swal-confirm="Hapus keahlian {{ $skill->name }} secara lunak? Histori pemetaan tetap tersedia." class="flex">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#e94f70] text-white transition hover:bg-[#d63d5e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e94f70] focus-visible:ring-offset-2" aria-label="Hapus keahlian {{ $skill->name }}" title="Hapus keahlian">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" /></svg>
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="p-8 text-center text-sm text-[#718088]">
                    {{ $search !== '' ? 'Tidak ada keahlian yang cocok dengan pencarian.' : 'Belum ada keahlian yang terdaftar.' }}
                </p>
            @endforelse
        </div>
    </section>

    <div id="skill-create-modal" data-ui-modal data-auto-open="{{ $autoOpenForm === 'create' && $errors->any() ? 'true' : 'false' }}" data-reset-on-close="true" data-clear-on-close="true" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
        <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
            <section role="dialog" aria-modal="true" aria-labelledby="skill-create-title" class="relative max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                <div class="sticky top-0 z-10 flex items-center justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                    <h2 id="skill-create-title" class="text-lg font-extrabold">Tambah Keahlian</h2>
                    <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup dialog tambah keahlian">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.skills.store') }}" data-ui-modal-form class="p-5 sm:p-6">
                    @csrf
                    <input type="hidden" name="_skill_form" value="create">
                    <div>
                        <div>
                            <label for="skill-create-name" class="ui-field-label">Nama Keahlian <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                            <input id="skill-create-name" data-ui-modal-focus name="name" type="text" value="{{ old('name') }}" autocomplete="off" required class="ui-input mt-2" placeholder="Contoh: Jaringan" @error('name') aria-invalid="true" aria-describedby="skill-create-name-error" @enderror>
                            @error('name')<p id="skill-create-name-error" data-ui-validation-error class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="skill-create-description" class="ui-field-label">Deskripsi <span class="font-normal text-[#78909a]">(opsional)</span></label>
                        <textarea id="skill-create-description" name="description" rows="3" class="ui-textarea mt-2" placeholder="Cakupan keahlian" @error('description') aria-invalid="true" aria-describedby="skill-create-description-error" @enderror>{{ old('description') }}</textarea>
                        @error('description')<p id="skill-create-description-error" data-ui-validation-error class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[#edf2f4] pt-4 sm:flex-row sm:justify-end">
                        <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Tutup</button>
                        <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary">
                            <span data-ui-modal-label>Simpan keahlian</span>
                            <span data-ui-modal-loading class="hidden">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    @foreach ($skills as $skill)
        @php($isEditingSkill = $autoOpenForm === 'edit-'.$skill->id && $errors->any())
        <div id="skill-edit-modal-{{ $skill->id }}" data-ui-modal data-auto-open="{{ $isEditingSkill ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
            <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
                <section role="dialog" aria-modal="true" aria-labelledby="skill-edit-title-{{ $skill->id }}" class="relative max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                    <div class="sticky top-0 z-10 flex items-center justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                        <h2 id="skill-edit-title-{{ $skill->id }}" class="text-lg font-extrabold">Edit Keahlian</h2>
                        <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup dialog edit keahlian {{ $skill->name }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.skills.update', $skill) }}" data-ui-modal-form class="p-5 sm:p-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_skill_form" value="edit-{{ $skill->id }}">
                        <input type="hidden" name="_skill_edit" value="{{ $skill->id }}">
                        <div>
                            <div>
                                <label for="skill-edit-name-{{ $skill->id }}" class="ui-field-label">Nama Keahlian <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                                <input id="skill-edit-name-{{ $skill->id }}" data-ui-modal-focus name="name" type="text" value="{{ $isEditingSkill ? old('name', $skill->name) : $skill->name }}" autocomplete="off" required class="ui-input mt-2" @error('name') aria-invalid="true" aria-describedby="skill-edit-name-error-{{ $skill->id }}" @enderror>
                                @error('name')<p id="skill-edit-name-error-{{ $skill->id }}" data-ui-validation-error class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="skill-edit-description-{{ $skill->id }}" class="ui-field-label">Deskripsi <span class="font-normal text-[#78909a]">(opsional)</span></label>
                            <textarea id="skill-edit-description-{{ $skill->id }}" name="description" rows="3" class="ui-textarea mt-2" @error('description') aria-invalid="true" aria-describedby="skill-edit-description-error-{{ $skill->id }}" @enderror>{{ $isEditingSkill ? old('description', $skill->description) : $skill->description }}</textarea>
                            @error('description')<p id="skill-edit-description-error-{{ $skill->id }}" data-ui-validation-error class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[#edf2f4] pt-4 sm:flex-row sm:justify-end">
                            <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Tutup</button>
                            <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary">
                                <span data-ui-modal-label>Simpan perubahan</span>
                                <span data-ui-modal-loading class="hidden">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    @endforeach
@endsection
