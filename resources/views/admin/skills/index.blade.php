@extends('layouts.app')

@php
    $activeSkills = $skills->where('is_active', true)->count();
    $mappedSkills = $skills->filter(fn ($skill) => $skill->serviceTypes->isNotEmpty())->count();
@endphp

@section('title', 'Data Keahlian — '.$branding['application_name'])
@section('header_kicker', 'Data Master')
@section('header_title', 'Data keahlian')

@section('content')
    <div class="ui-page-header">
        <div>
            <h1 class="ui-page-title">Data Keahlian</h1>
            <p class="ui-page-description">Kelola master bidang keahlian. Pemetaan penggunaannya diatur langsung pada Layanan &amp; formulir.</p>
        </div>
        <a href="{{ route('admin.catalog.index', ['section' => 'services']) }}" class="ui-btn ui-btn-secondary w-full shrink-0 sm:w-auto">Atur pemetaan di Layanan &amp; formulir <span aria-hidden="true">→</span></a>
    </div>

    <section class="mt-8 grid gap-3 sm:grid-cols-3" aria-label="Ringkasan data keahlian">
        <article class="ui-stat-card">
            <span class="ui-stat-icon" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.8 14.1 9l5.4.45-4.12 3.5 1.24 5.25L12 15.35l-4.62 2.85 1.24-5.25-4.12-3.5L9.9 9 12 3.8Z" /></svg>
            </span>
            <div><p class="ui-stat-label">Total keahlian</p><p class="ui-stat-value">{{ $skills->count() }}</p></div>
        </article>
        <article class="ui-stat-card">
            <span class="ui-stat-icon !bg-[#e8faf4] !text-[#087f5b]" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
            </span>
            <div><p class="ui-stat-label">Keahlian aktif</p><p class="ui-stat-value">{{ $activeSkills }}</p></div>
        </article>
        <article class="ui-stat-card">
            <span class="ui-stat-icon !bg-[#eef3ff] !text-[#4f63a6]" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14v13H5zM8 9h8M8 12h8M8 15h5" /></svg>
            </span>
            <div><p class="ui-stat-label">Terpetakan ke layanan</p><p class="ui-stat-value">{{ $mappedSkills }}</p></div>
        </article>
    </section>

    <section class="ui-panel mt-8 overflow-hidden" aria-labelledby="skills-heading">
        <div class="ui-panel-header flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h2 id="skills-heading" class="ui-section-title">Daftar keahlian</h2>
                <p class="ui-section-description">Pastikan nama keahlian mudah dikenali oleh agen dan relevan dengan layanan yang ditangani.</p>
            </div>
            <button type="button" data-ui-modal-open="skill-create-modal" class="ui-btn ui-btn-primary w-full shrink-0 sm:w-auto">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Tambah keahlian
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="ui-table min-w-[58rem]">
                <caption class="sr-only">Daftar keahlian, layanan terpetakan, status, dan aksi</caption>
                <thead>
                    <tr>
                        <th scope="col">Keahlian</th>
                        <th scope="col">Layanan terpetakan</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($skills as $skill)
                        <tr>
                            <td>
                                <div class="min-w-[16rem]">
                                    <p class="font-bold text-[#17313c]">{{ $skill->name }}</p>
                                    <p class="mt-1 text-xs text-[#78909a]">Kode: {{ $skill->slug ?: 'Belum ada kode' }}</p>
                                    @if ($skill->description)
                                        <p class="mt-1 max-w-[24rem] truncate text-xs text-[#78909a]">{{ $skill->description }}</p>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex max-w-[22rem] flex-wrap gap-1.5">
                                    @forelse ($skill->serviceTypes->take(3) as $serviceType)
                                        <span class="ui-chip">{{ $serviceType->code }} · {{ $serviceType->name }}</span>
                                    @empty
                                        <span class="text-xs text-[#78909a]">Belum ada layanan</span>
                                    @endforelse
                                    @if ($skill->serviceTypes->count() > 3)
                                        <span class="ui-chip !border-[#dce7eb] !bg-[#f5f8f9] !text-[#6a8089]">+{{ $skill->serviceTypes->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="ui-status {{ $skill->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $skill->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="text-right">
                                <div class="flex min-w-[16rem] flex-wrap justify-end gap-2">
                                    <button type="button" data-ui-modal-open="skill-edit-modal-{{ $skill->id }}" class="ui-btn ui-btn-ghost !min-h-9 !px-3 !text-xs">Edit</button>
                                    @if ($skill->is_active)
                                        <button type="submit" form="deactivate-skill-{{ $skill->id }}" class="ui-btn ui-btn-warning !min-h-9 !px-3 !text-xs">Nonaktifkan</button>
                                    @else
                                        <button type="submit" form="activate-skill-{{ $skill->id }}" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs">Aktifkan</button>
                                    @endif
                                    <button type="submit" form="delete-skill-{{ $skill->id }}" class="ui-btn ui-btn-danger !min-h-9 !px-3 !text-xs">Hapus</button>
                                </div>
                                <form id="activate-skill-{{ $skill->id }}" method="POST" action="{{ route('admin.skills.activate', $skill) }}" class="hidden">@csrf</form>
                                    <form id="deactivate-skill-{{ $skill->id }}" method="POST" action="{{ route('admin.skills.deactivate', $skill) }}" class="hidden" onsubmit="return confirm('Nonaktifkan keahlian ini? Pemetaan dan histori yang ada tetap disimpan.');">@csrf</form>
                                <form id="delete-skill-{{ $skill->id }}" method="POST" action="{{ route('admin.skills.destroy', $skill) }}" class="hidden" onsubmit="return confirm('Hapus keahlian secara lunak? Histori pemetaan tetap tersedia.');">@csrf @method('DELETE')</form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="ui-empty m-4">Belum ada data keahlian. Gunakan tombol <span class="font-bold">Tambah keahlian</span> untuk menambahkan data pertama.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div id="skill-create-modal" data-ui-modal data-auto-open="{{ old('_skill_create') ? 'true' : 'false' }}" data-reset-on-close="true" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <button type="button" data-ui-modal-close class="absolute inset-0 cursor-default bg-slate-950/40" tabindex="-1" aria-label="Tutup dialog"></button>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <section role="dialog" aria-modal="true" aria-labelledby="skill-create-title" aria-describedby="skill-create-description" class="relative max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-2xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                <div class="flex items-center justify-between gap-4 border-b border-[#e7eef1] px-5 py-4 sm:px-6">
                    <div>
                        <h2 id="skill-create-title" class="text-base font-extrabold text-[#17313c]">Tambah keahlian</h2>
                        <p id="skill-create-description" class="mt-1 text-xs text-[#78909a]">Isi data keahlian yang akan digunakan pada pemetaan triase.</p>
                    </div>
                    <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[#78909a] transition hover:bg-[#f4f8f9] hover:text-[#35505b] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2bb8aa]" aria-label="Tutup">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.skills.store') }}" data-ui-modal-form class="p-5 sm:p-6">
                    @csrf
                    <input type="hidden" name="_skill_create" value="1">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="skill-create-name" class="ui-field-label">Nama keahlian <span class="text-rose-600">*</span></label>
                            <input id="skill-create-name" data-ui-modal-focus name="name" value="{{ old('name') }}" required class="ui-input mt-2" placeholder="Contoh: Jaringan" @error('name') aria-invalid="true" aria-describedby="skill-create-name-error" @enderror>
                            @error('name')<p id="skill-create-name-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="skill-create-slug" class="ui-field-label">Kode <span class="font-normal text-[#78909a]">(opsional)</span></label>
                            <input id="skill-create-slug" name="slug" value="{{ old('slug') }}" class="ui-input mt-2" placeholder="jaringan" @error('slug') aria-invalid="true" aria-describedby="skill-create-slug-error" @enderror>
                            @error('slug')<p id="skill-create-slug-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="skill-create-description" class="ui-field-label">Deskripsi <span class="font-normal text-[#78909a]">(opsional)</span></label>
                        <textarea id="skill-create-description" name="description" rows="3" class="ui-textarea mt-2" placeholder="Cakupan keahlian" @error('description') aria-invalid="true" aria-describedby="skill-create-description-error" @enderror>{{ old('description') }}</textarea>
                        @error('description')<p id="skill-create-description-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Batal</button>
                        <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary">
                            <span data-ui-modal-label>Tambah keahlian</span>
                            <span data-ui-modal-loading class="hidden">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    @foreach ($skills as $skill)
        @php($isEditingSkill = (string) old('_skill_edit') === (string) $skill->id)
        <div id="skill-edit-modal-{{ $skill->id }}" data-ui-modal data-auto-open="{{ $isEditingSkill ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
            <button type="button" data-ui-modal-close class="absolute inset-0 cursor-default bg-slate-950/40" tabindex="-1" aria-label="Tutup dialog"></button>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <section role="dialog" aria-modal="true" aria-labelledby="skill-edit-title-{{ $skill->id }}" class="relative max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-2xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                    <div class="flex items-center justify-between gap-4 border-b border-[#e7eef1] px-5 py-4 sm:px-6">
                        <div>
                            <h2 id="skill-edit-title-{{ $skill->id }}" class="text-base font-extrabold text-[#17313c]">Edit keahlian</h2>
                            <p class="mt-1 text-xs text-[#78909a]">Perbarui detail {{ $skill->name }}.</p>
                        </div>
                        <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[#78909a] transition hover:bg-[#f4f8f9] hover:text-[#35505b] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2bb8aa]" aria-label="Tutup">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.skills.update', $skill) }}" data-ui-modal-form class="p-5 sm:p-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_skill_edit" value="{{ $skill->id }}">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="skill-edit-name-{{ $skill->id }}" class="ui-field-label">Nama keahlian <span class="text-rose-600">*</span></label>
                                <input id="skill-edit-name-{{ $skill->id }}" data-ui-modal-focus name="name" value="{{ $isEditingSkill ? old('name', $skill->name) : $skill->name }}" required class="ui-input mt-2">
                                @error('name')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="skill-edit-slug-{{ $skill->id }}" class="ui-field-label">Kode <span class="font-normal text-[#78909a]">(opsional)</span></label>
                                <input id="skill-edit-slug-{{ $skill->id }}" name="slug" value="{{ $isEditingSkill ? old('slug', $skill->slug) : $skill->slug }}" class="ui-input mt-2">
                                @error('slug')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="skill-edit-description-{{ $skill->id }}" class="ui-field-label">Deskripsi <span class="font-normal text-[#78909a]">(opsional)</span></label>
                            <textarea id="skill-edit-description-{{ $skill->id }}" name="description" rows="3" class="ui-textarea mt-2">{{ $isEditingSkill ? old('description', $skill->description) : $skill->description }}</textarea>
                            @error('description')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Batal</button>
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
