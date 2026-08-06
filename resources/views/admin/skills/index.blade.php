@extends('layouts.app')

@php
    $activeSkills = $skills->where('is_active', true)->count();
    $activeCategories = $categories->where('is_active', true)->count();
    $mappedCategories = $categories->filter(fn ($category) => $category->skills->isNotEmpty())->count();
@endphp

@section('title', 'Data Keahlian — SIHATI')
@section('header_kicker', 'Administrasi master data')
@section('header_title', 'Keahlian dan kategori')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Master data triase</p>
            <h1 class="ui-page-title">Keahlian dan kategori masalah</h1>
            <p class="ui-page-description">Kelola kosakata yang dipakai untuk memetakan masalah ke teknisi. Detail edit disimpan tertutup agar halaman tetap mudah dipindai.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="ui-btn ui-btn-ghost">Kembali ke pengguna <span aria-hidden="true">→</span></a>
    </div>

    <section class="mt-8 grid gap-3 sm:grid-cols-3" aria-label="Ringkasan master data">
        <article class="ui-stat-card"><span class="ui-stat-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.8 14.1 9l5.4.45-4.12 3.5 1.24 5.25L12 15.35l-4.62 2.85 1.24-5.25-4.12-3.5L9.9 9 12 3.8Z" /></svg></span><div><p class="ui-stat-label">Keahlian aktif</p><p class="ui-stat-value">{{ $activeSkills }}</p></div></article>
        <article class="ui-stat-card"><span class="ui-stat-icon !bg-[#eef3ff] !text-[#4f63a6]" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14v13H5zM8 9h8M8 12h8M8 15h5" /></svg></span><div><p class="ui-stat-label">Kategori aktif</p><p class="ui-stat-value">{{ $activeCategories }}</p></div></article>
        <article class="ui-stat-card"><span class="ui-stat-icon !bg-[#fff6df] !text-[#a16207]" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7.5h10M7 12h10M7 16.5h6" /><path stroke-linecap="round" d="M4.5 4.5h.01M4.5 9h.01M4.5 13.5h.01" /></svg></span><div><p class="ui-stat-label">Kategori terpetakan</p><p class="ui-stat-value">{{ $mappedCategories }}</p></div></article>
    </section>

    <div class="mt-8 grid items-start gap-5 xl:grid-cols-2">
        <section class="ui-panel overflow-hidden" aria-labelledby="skills-heading">
            <div class="ui-panel-header flex items-start justify-between gap-4">
                <div>
                    <h2 id="skills-heading" class="ui-section-title">Bidang keahlian</h2>
                    <p class="ui-section-description">Keahlian aktif dapat diberikan kepada pengguna.</p>
                </div>
                <span class="rounded-full bg-[#effcf9] px-2.5 py-1 text-xs font-extrabold text-[#0f766e]">{{ $skills->count() }}</span>
            </div>

            <form method="POST" action="{{ route('admin.skills.store') }}" class="m-4 rounded-xl border border-[#b9e8e1] bg-[#ecfbf8] p-4 sm:m-5">
                @csrf
                <div class="flex items-center justify-between gap-3">
                    <div><p class="text-sm font-extrabold text-[#17313c]">Tambah keahlian</p><p class="mt-1 text-xs text-[#52747b]">Gunakan nama yang mudah dikenali agen.</p></div>
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white text-[#0f766e]" aria-hidden="true"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg></span>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div><label for="new-skill-name" class="ui-field-label">Nama keahlian <span class="text-rose-600">*</span></label><input id="new-skill-name" name="name" value="{{ old('name') }}" required class="ui-input mt-2" placeholder="Contoh: Jaringan"></div>
                    <div><label for="new-skill-slug" class="ui-field-label">Kode <span class="font-normal text-[#78909a]">(opsional)</span></label><input id="new-skill-slug" name="slug" value="{{ old('slug') }}" class="ui-input mt-2" placeholder="jaringan"></div>
                </div>
                <div class="mt-3"><label for="new-skill-description" class="ui-field-label">Deskripsi <span class="font-normal text-[#78909a]">(opsional)</span></label><textarea id="new-skill-description" name="description" rows="2" class="ui-textarea mt-2" placeholder="Cakupan keahlian">{{ old('description') }}</textarea></div>
                <button type="submit" class="ui-btn ui-btn-primary mt-3 !min-h-9 !text-xs">Tambah keahlian</button>
            </form>

            <div class="space-y-2 p-4 pt-0 sm:p-5 sm:pt-0">
                @forelse ($skills as $skill)
                    <details class="rounded-xl border border-[#e1eaed] bg-white">
                        <summary class="ui-disclosure-summary flex items-center justify-between gap-3 p-4">
                            <span class="flex min-w-0 items-center gap-3">
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#f1f7f7] text-[#0f766e]" aria-hidden="true"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.8 14.1 9l5.4.45-4.12 3.5 1.24 5.25L12 15.35l-4.62 2.85 1.24-5.25-4.12-3.5L9.9 9 12 3.8Z" /></svg></span>
                                <span class="min-w-0"><span class="block truncate text-sm font-extrabold text-[#35505b]">{{ $skill->name }}</span><span class="mt-1 block truncate text-[0.68rem] text-[#78909a]">{{ $skill->slug }} · {{ $skill->problemCategories->count() }} kategori terpetakan</span></span>
                            </span>
                            <span class="ui-status {{ $skill->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $skill->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </summary>
                        <div class="border-t border-[#edf2f4] p-4">
                            <form method="POST" action="{{ route('admin.skills.update', $skill) }}" class="space-y-3">
                                @csrf
                                @method('PUT')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div><label for="skill-name-{{ $skill->id }}" class="ui-field-label">Nama keahlian</label><input id="skill-name-{{ $skill->id }}" name="name" value="{{ $skill->name }}" required class="ui-input mt-2"></div>
                                    <div><label for="skill-slug-{{ $skill->id }}" class="ui-field-label">Kode keahlian</label><input id="skill-slug-{{ $skill->id }}" name="slug" value="{{ $skill->slug }}" required class="ui-input mt-2"></div>
                                </div>
                                <div><label for="skill-description-{{ $skill->id }}" class="ui-field-label">Deskripsi</label><textarea id="skill-description-{{ $skill->id }}" name="description" rows="2" class="ui-textarea mt-2">{{ $skill->description }}</textarea></div>
                                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[#edf2f4] pt-3">
                                    <button type="submit" class="ui-btn ui-btn-primary !min-h-9 !text-xs">Simpan perubahan</button>
                                    <div class="flex flex-wrap gap-2">
                                        @if ($skill->is_active)
                                            <button type="submit" form="deactivate-skill-{{ $skill->id }}" class="ui-btn ui-btn-warning !min-h-9 !px-3 !text-xs">Nonaktifkan</button>
                                        @else
                                            <button type="submit" form="activate-skill-{{ $skill->id }}" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs">Aktifkan</button>
                                        @endif
                                        <button type="submit" form="delete-skill-{{ $skill->id }}" class="ui-btn ui-btn-danger !min-h-9 !px-3 !text-xs">Hapus</button>
                                    </div>
                                </div>
                            </form>
                            <form id="activate-skill-{{ $skill->id }}" method="POST" action="{{ route('admin.skills.activate', $skill) }}" class="hidden">@csrf</form>
                            <form id="deactivate-skill-{{ $skill->id }}" method="POST" action="{{ route('admin.skills.deactivate', $skill) }}" class="hidden" onsubmit="return confirm('Nonaktifkan keahlian ini? Mapping dan histori yang ada tetap disimpan.');">@csrf</form>
                            <form id="delete-skill-{{ $skill->id }}" method="POST" action="{{ route('admin.skills.destroy', $skill) }}" class="hidden" onsubmit="return confirm('Hapus keahlian secara lunak? Histori pemetaan tetap tersedia.');">@csrf @method('DELETE')</form>
                        </div>
                    </details>
                @empty
                    <div class="ui-empty">Belum ada bidang keahlian.</div>
                @endforelse
            </div>
        </section>

        <section class="ui-panel overflow-hidden" aria-labelledby="categories-heading">
            <div class="ui-panel-header flex items-start justify-between gap-4">
                <div>
                    <h2 id="categories-heading" class="ui-section-title">Kategori masalah</h2>
                    <p class="ui-section-description">Pemetaan kategori menjadi dasar saran teknisi.</p>
                </div>
                <span class="rounded-full bg-[#eef3ff] px-2.5 py-1 text-xs font-extrabold text-[#4f63a6]">{{ $categories->count() }}</span>
            </div>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="m-4 rounded-xl border border-[#d8e0fb] bg-[#f4f6ff] p-4 sm:m-5">
                @csrf
                <div class="flex items-center justify-between gap-3">
                    <div><p class="text-sm font-extrabold text-[#293b66]">Tambah kategori</p><p class="mt-1 text-xs text-[#68779d]">Kelompokkan jenis masalah yang sering masuk.</p></div>
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white text-[#5368b0]" aria-hidden="true"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg></span>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div><label for="new-category-name" class="ui-field-label">Nama kategori <span class="text-rose-600">*</span></label><input id="new-category-name" name="name" value="{{ old('name') }}" required class="ui-input mt-2" placeholder="Contoh: Akses jaringan"></div>
                    <div><label for="new-category-slug" class="ui-field-label">Kode <span class="font-normal text-[#78909a]">(opsional)</span></label><input id="new-category-slug" name="slug" value="{{ old('slug') }}" class="ui-input mt-2" placeholder="akses-jaringan"></div>
                </div>
                <div class="mt-3"><label for="new-category-description" class="ui-field-label">Deskripsi <span class="font-normal text-[#78909a]">(opsional)</span></label><textarea id="new-category-description" name="description" rows="2" class="ui-textarea mt-2" placeholder="Penjelasan kategori">{{ old('description') }}</textarea></div>
                <button type="submit" class="ui-btn ui-btn-primary mt-3 !min-h-9 !text-xs">Tambah kategori</button>
            </form>

            <div class="space-y-2 p-4 pt-0 sm:p-5 sm:pt-0">
                @forelse ($categories as $category)
                    @php($categorySkillIds = $category->skills->pluck('id')->map(fn ($id) => (string) $id)->all())
                    <details class="rounded-xl border border-[#e1eaed] bg-white">
                        <summary class="ui-disclosure-summary flex items-center justify-between gap-3 p-4">
                            <span class="flex min-w-0 items-center gap-3">
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#f4f6ff] text-[#5368b0]" aria-hidden="true"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14v13H5zM8 9h8M8 12h8M8 15h5" /></svg></span>
                                <span class="min-w-0"><span class="block truncate text-sm font-extrabold text-[#35505b]">{{ $category->name }}</span><span class="mt-1 block truncate text-[0.68rem] text-[#78909a]">{{ $category->slug }} · {{ $category->skills->count() }} keahlian terpetakan</span></span>
                            </span>
                            <span class="ui-status {{ $category->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </summary>
                        <div class="border-t border-[#edf2f4] p-4">
                            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-3">
                                @csrf
                                @method('PUT')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div><label for="category-name-{{ $category->id }}" class="ui-field-label">Nama kategori</label><input id="category-name-{{ $category->id }}" name="name" value="{{ $category->name }}" required class="ui-input mt-2"></div>
                                    <div><label for="category-slug-{{ $category->id }}" class="ui-field-label">Kode kategori</label><input id="category-slug-{{ $category->id }}" name="slug" value="{{ $category->slug }}" required class="ui-input mt-2"></div>
                                </div>
                                <div><label for="category-description-{{ $category->id }}" class="ui-field-label">Deskripsi</label><textarea id="category-description-{{ $category->id }}" name="description" rows="2" class="ui-textarea mt-2">{{ $category->description }}</textarea></div>
                                <button type="submit" class="ui-btn ui-btn-primary !min-h-9 !text-xs">Simpan detail kategori</button>
                            </form>

                            <form method="POST" action="{{ route('admin.categories.skills.update', $category) }}" class="mt-5 border-t border-[#edf2f4] pt-4">
                                @csrf
                                @method('PUT')
                                <fieldset>
                                    <legend class="text-sm font-extrabold text-[#35505b]">Pemetaan keahlian</legend>
                                    <p class="mt-1 text-xs leading-5 text-[#78909a]">Pilih keahlian yang relevan untuk kategori ini.</p>
                                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                        @forelse ($skills->where('is_active', true) as $skill)
                                            <label class="flex items-center gap-2.5 rounded-lg border border-[#e1eaed] px-3 py-2.5 text-xs font-semibold text-[#45606a] transition hover:border-[#8bd6cc] hover:bg-[#f4fcfa]">
                                                <input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}" @checked(in_array((string) $skill->id, $categorySkillIds, true)) class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                                                <span>{{ $skill->name }}</span>
                                            </label>
                                        @empty
                                            <p class="text-sm text-[#78909a]">Tambahkan keahlian aktif terlebih dahulu.</p>
                                        @endforelse
                                    </div>
                                </fieldset>
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                                    <button type="submit" class="ui-btn ui-btn-secondary !min-h-9 !text-xs">Simpan pemetaan</button>
                                    <div class="flex flex-wrap gap-2">
                                        @if ($category->is_active)
                                            <button type="submit" form="deactivate-category-{{ $category->id }}" class="ui-btn ui-btn-warning !min-h-9 !px-3 !text-xs">Nonaktifkan</button>
                                        @else
                                            <button type="submit" form="activate-category-{{ $category->id }}" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs">Aktifkan</button>
                                        @endif
                                        <button type="submit" form="delete-category-{{ $category->id }}" class="ui-btn ui-btn-danger !min-h-9 !px-3 !text-xs">Hapus</button>
                                    </div>
                                </div>
                            </form>
                            <form id="activate-category-{{ $category->id }}" method="POST" action="{{ route('admin.categories.activate', $category) }}" class="hidden">@csrf</form>
                            <form id="deactivate-category-{{ $category->id }}" method="POST" action="{{ route('admin.categories.deactivate', $category) }}" class="hidden" onsubmit="return confirm('Nonaktifkan kategori ini? Histori pemetaan tetap tersedia.');">@csrf</form>
                            <form id="delete-category-{{ $category->id }}" method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="hidden" onsubmit="return confirm('Hapus kategori secara lunak? Histori pemetaan tetap tersedia.');">@csrf @method('DELETE')</form>
                        </div>
                    </details>
                @empty
                    <div class="ui-empty">Belum ada kategori masalah.</div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
