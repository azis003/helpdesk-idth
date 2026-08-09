@extends('layouts.app')

@php
    $previewTagline = $branding['tagline'] ?: 'Portal layanan internal';
    $previewFooter = $branding['footer_text'] ?: 'Portal layanan TI internal';
    $activeVersionLabel = $branding['is_default'] ? 'Nilai awal' : 'Versi aktif v'.$branding['version'];
@endphp

@section('title', 'Identitas aplikasi — '.$branding['application_name'])
@section('header_kicker', 'Manajemen aplikasi')
@section('header_title', 'Identitas aplikasi')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#ffd44f] !shadow-[0_0_0_4px_#fff4cf]" aria-hidden="true"></span>Manajemen Aplikasi</p>
            <h1 class="ui-page-title">Identitas aplikasi</h1>
            <p class="ui-page-description">Kelola nama, logo, dan teks yang tampil di aplikasi.</p>
        </div>
    </div>

    <div class="mt-7 grid items-start gap-5 xl:grid-cols-[minmax(0,1.15fr)_minmax(22rem,0.85fr)]">
        <section class="ui-panel overflow-hidden" aria-labelledby="branding-form-heading">
            <div class="ui-panel-header flex flex-wrap items-center justify-between gap-3">
                <h2 id="branding-form-heading" class="ui-section-title">Pengaturan identitas</h2>
                <span class="ui-status {{ $branding['is_default'] ? 'ui-status-warning' : 'ui-status-active' }}">{{ $activeVersionLabel }}</span>
            </div>

            <form method="POST" action="{{ route('admin.branding.update') }}" enctype="multipart/form-data" class="space-y-7 p-5 sm:p-6" data-branding-form data-branding-default-tagline="{{ $previewTagline }}" data-branding-default-footer="{{ $previewFooter }}">
                @csrf
                @method('PUT')

                <fieldset class="space-y-4">
                    <legend class="text-sm font-extrabold tracking-[-0.015em] text-[#17313c]">Nama aplikasi</legend>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form-field
                            name="organization_name"
                            label="Nama instansi"
                            :value="$branding['organization_name']"
                            help="Nama organisasi pemilik layanan."
                            placeholder="Contoh: PT Contoh Indonesia"
                            data-branding-field="organization_name"
                            required
                        />
                        <x-form-field
                            name="application_name"
                            label="Nama aplikasi"
                            :value="$branding['application_name']"
                            help="Tampil di menu, judul halaman, dan laporan."
                            placeholder="Contoh: Service Desk"
                            data-branding-field="application_name"
                            required
                        />
                    </div>
                </fieldset>

                <fieldset class="space-y-4 border-t border-[#e7eef1] pt-6">
                    <legend class="text-sm font-extrabold tracking-[-0.015em] text-[#17313c]">Pesan portal</legend>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form-field
                            name="tagline"
                            label="Tagline portal"
                            :value="$branding['tagline']"
                            help="Tampil sebagai sambutan di halaman masuk."
                            placeholder="Contoh: Satu pintu dukungan TI"
                            data-branding-field="tagline"
                        />
                        <x-form-field
                            name="footer_text"
                            label="Teks halaman masuk"
                            :value="$branding['footer_text']"
                            help="Tampil di bagian bawah halaman masuk."
                            placeholder="Contoh: Portal layanan TI internal"
                            data-branding-field="footer_text"
                        />
                    </div>
                </fieldset>

                <fieldset class="space-y-4 border-t border-[#e7eef1] pt-6">
                    <legend class="text-sm font-extrabold tracking-[-0.015em] text-[#17313c]">Logo instansi</legend>

                    <div class="grid gap-4 md:grid-cols-[10rem_minmax(0,1fr)] md:items-center">
                        <div class="ui-branding-logo-stage" aria-label="Logo yang sedang dipakai">
                            @if ($branding['logo_url'])
                                <img src="{{ $branding['logo_url'] }}" alt="Pratinjau logo {{ $branding['organization_name'] }}" class="max-h-full max-w-full object-contain" data-branding-preview-image data-branding-initial-src="{{ $branding['logo_url'] }}">
                                <span class="ui-brand-mark hidden !h-14 !w-14 !rounded-xl text-base" data-branding-preview-fallback aria-hidden="true">{{ $branding['monogram'] }}</span>
                            @else
                                <img src="" alt="" class="hidden max-h-full max-w-full object-contain" data-branding-preview-image>
                                <span class="ui-brand-mark !h-14 !w-14 !rounded-xl text-base" data-branding-preview-fallback aria-hidden="true">{{ $branding['monogram'] }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <label for="branding-logo" class="ui-field-label">Pilih logo baru <span class="font-normal text-[#8aa0a8]">(opsional)</span></label>
                            <p id="branding-logo-help" class="ui-field-help">JPG, PNG, atau WebP · maksimal 2 MB · ukuran maksimal 1600 × 600 piksel.</p>
                            <input id="branding-logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="ui-file-input mt-3" aria-describedby="branding-logo-help branding-logo-file branding-logo-file-error" data-branding-logo-input>
                            <p id="branding-logo-file" class="mt-2 text-xs text-[#78909a]" data-branding-file-name aria-live="polite">Belum ada file baru yang dipilih.</p>
                            @error('logo')
                                <p id="branding-logo-file-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                            @if ($branding['logo_path'])
                                <label class="mt-4 flex min-h-11 cursor-pointer items-start gap-3 rounded-xl border border-[#e7d7b2] bg-[#fffaf0] px-3 py-2.5 text-xs text-[#765c26] transition hover:border-[#d8bb73]">
                                    <input type="checkbox" name="remove_logo" value="1" class="ui-checkbox mt-0.5" @checked(old('remove_logo')) data-branding-remove-logo>
                                    <span>
                                        <span class="block font-extrabold">Hapus logo aktif</span>
                                        <span class="mt-0.5 block leading-5">Jika disimpan, monogram aplikasi akan digunakan sebagai pengganti logo.</span>
                                    </span>
                                </label>
                            @endif
                        </div>
                    </div>
                </fieldset>

                <div class="flex justify-end border-t border-[#e7eef1] pt-5">
                    <button type="submit" class="ui-btn ui-btn-primary shrink-0" data-branding-submit>
                        <span data-branding-submit-label>Simpan perubahan</span>
                        <span class="hidden" data-branding-submit-loading aria-hidden="true">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </section>

        <aside class="ui-panel overflow-hidden xl:sticky xl:top-24" aria-labelledby="branding-preview-heading">
            <div class="ui-panel-header">
                <h2 id="branding-preview-heading" class="ui-section-title">Pratinjau</h2>
            </div>
            <div class="p-5 sm:p-6">
                <div class="ui-branding-preview" aria-label="Pratinjau halaman masuk">
                    <div class="h-1 bg-[#ffd438]" aria-hidden="true"></div>
                    <div class="p-5 sm:p-6">
                        <div class="flex items-center gap-3">
                            @if ($branding['logo_url'])
                                <img src="{{ $branding['logo_url'] }}" alt="Pratinjau logo instansi" class="h-12 max-w-32 object-contain" data-branding-preview-image data-branding-initial-src="{{ $branding['logo_url'] }}">
                                <span class="ui-brand-mark hidden !h-12 !w-12 !rounded-lg text-sm" data-branding-preview-fallback aria-hidden="true">{{ $branding['monogram'] }}</span>
                            @else
                                <img src="" alt="" class="hidden h-12 max-w-32 object-contain" data-branding-preview-image>
                                <span class="ui-brand-mark !h-12 !w-12 !rounded-lg text-sm" data-branding-preview-fallback aria-hidden="true">{{ $branding['monogram'] }}</span>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate text-sm font-extrabold tracking-[-0.03em] text-[#18252b]" data-branding-preview-application>{{ $branding['application_name'] }}</p>
                                <p class="mt-1 truncate text-[0.68rem] text-[#829198]" data-branding-preview-organization>{{ $branding['organization_name'] }}</p>
                            </div>
                        </div>

                        <div class="mt-10">
                            <p class="text-[0.62rem] font-extrabold uppercase tracking-[0.14em] text-[#8b9a9f]">Selamat datang</p>
                            <p class="mt-2 text-lg font-semibold leading-snug tracking-tight text-[#3f4548]" data-branding-preview-tagline>{{ $previewTagline }}</p>
                        </div>

                        <div class="mt-8 border-t border-[#edf2f4] pt-4 text-[0.68rem] leading-5 text-[#8a999e]" data-branding-preview-footer>{{ $previewFooter }}</div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <section class="ui-panel mt-5 overflow-hidden" aria-labelledby="branding-history-heading">
        <div class="ui-panel-header">
            <h2 id="branding-history-heading" class="ui-section-title">Riwayat identitas</h2>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="ui-table w-full text-left text-sm">
                <caption class="sr-only">Daftar perubahan identitas aplikasi</caption>
                <thead>
                    <tr>
                        <th scope="col">Versi</th>
                        <th scope="col">Informasi diubah</th>
                        <th scope="col">Diubah oleh</th>
                        <th scope="col">Waktu perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($versions as $version)
                        <tr>
                            <td class="whitespace-nowrap font-extrabold text-[#35505b]">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span>v{{ $version->version }}</span>
                                    @if ($version->is_active)
                                        <span class="ui-status ui-status-active">Aktif sekarang</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($version->changed_fields as $changedField)
                                        <span class="ui-chip">{{ $changedField }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-[#526b75]">{{ $version->changedBy?->name ?? 'Default sistem' }}</td>
                            <td class="whitespace-nowrap text-[#78909a]">{{ $version->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="ui-empty m-4">
                                    <h3 class="text-sm font-extrabold text-[#526f79]">Belum ada perubahan tersimpan</h3>
                                    <p class="mx-auto mt-2 max-w-md text-xs leading-5 text-[#78909a]">Aplikasi masih menggunakan nilai awal. Simpan perubahan pertama untuk mulai membangun riwayat identitas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-[#edf2f4] md:hidden">
            @forelse ($versions as $version)
                <article class="space-y-3 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-extrabold text-[#35505b]">Versi {{ $version->version }}</p>
                            <div class="flex flex-wrap gap-2 pt-1">
                                @foreach ($version->changed_fields as $changedField)
                                    <span class="ui-chip">{{ $changedField }}</span>
                                @endforeach
                            </div>
                        </div>
                        @if ($version->is_active)
                            <span class="ui-status ui-status-active">Aktif</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-[#78909a]">
                        <span>{{ $version->changedBy?->name ?? 'Default sistem' }}</span>
                        <span>{{ $version->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</span>
                    </div>
                </article>
            @empty
                <div class="ui-empty m-4">
                    <h3 class="text-sm font-extrabold text-[#526f79]">Belum ada perubahan tersimpan</h3>
                    <p class="mx-auto mt-2 max-w-md text-xs leading-5 text-[#78909a]">Nilai awal masih digunakan sampai Anda menyimpan identitas baru.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
