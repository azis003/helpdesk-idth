@extends('layouts.app')

@section('title', 'Identitas aplikasi — '.$branding['application_name'])
@section('header_kicker', 'Administrasi')
@section('header_title', 'Identitas aplikasi')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#ffd44f] !shadow-[0_0_0_4px_#fff4cf]" aria-hidden="true"></span>Konfigurasi instansi</p>
            <h1 class="ui-page-title">Identitas aplikasi</h1>
            <p class="ui-page-description">Atur nama instansi, logo, dan teks yang tampil di ruang kerja, halaman masuk, serta laporan tanpa mengubah kode.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="ui-btn ui-btn-ghost">Kembali ke dasbor <span aria-hidden="true">→</span></a>
    </div>

    <div class="mt-8 grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(18rem,0.65fr)]">
        <section class="ui-panel overflow-hidden" aria-labelledby="branding-form-heading">
            <div class="ui-panel-header">
                <div>
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Pengaturan aktif</p>
                    <h2 id="branding-form-heading" class="ui-section-title">Nama dan tampilan</h2>
                    <p class="ui-section-description">Nilai yang disimpan menjadi versi baru. Versi sebelumnya tetap tersedia untuk penelusuran.</p>
                </div>
                <span class="ui-chip">{{ $branding['is_default'] ? 'Default aman' : 'Versi '.$branding['version'] }}</span>
            </div>

            <form method="POST" action="{{ route('admin.branding.update') }}" enctype="multipart/form-data" class="space-y-6 p-5 sm:p-6" data-branding-form>
                @csrf
                @method('PUT')

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form-field
                        name="organization_name"
                        label="Nama instansi"
                        :value="$branding['organization_name']"
                        help="Nama organisasi yang memiliki layanan ini."
                        required
                    />
                    <x-form-field
                        name="application_name"
                        label="Nama aplikasi"
                        :value="$branding['application_name']"
                        help="Nama pendek untuk judul dan navigasi aplikasi."
                        required
                    />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form-field
                        name="tagline"
                        label="Tagline portal"
                        :value="$branding['tagline']"
                        help="Teks pendamping nama aplikasi, misalnya Portal Layanan TI."
                    />
                    <x-form-field
                        name="footer_text"
                        label="Teks footer"
                        :value="$branding['footer_text']"
                        help="Keterangan singkat yang tampil pada halaman masuk."
                    />
                </div>

                <div>
                    <label for="branding-logo" class="ui-field-label">Logo instansi</label>
                    <p id="branding-logo-help" class="ui-field-help">Gunakan JPG, PNG, atau WebP maksimal 2 MB dan 1600 × 600 piksel. File disimpan pada storage privat.</p>
                    <div class="mt-3 grid gap-4 sm:grid-cols-[auto_minmax(0,1fr)] sm:items-center">
                        <div class="flex h-24 w-32 items-center justify-center overflow-hidden rounded-xl border border-[#dce9ed] bg-[#f8fbfc] p-3" data-branding-preview>
                            @if ($branding['logo_url'])
                                <img src="{{ $branding['logo_url'] }}" alt="Logo {{ $branding['organization_name'] }}" class="max-h-full max-w-full object-contain" data-branding-preview-image>
                            @else
                                <img src="" alt="" class="hidden max-h-full max-w-full object-contain" data-branding-preview-image>
                                <span class="ui-brand-mark !h-14 !w-14 !rounded-xl text-base" data-branding-preview-fallback>{{ $branding['monogram'] }}</span>
                            @endif
                        </div>
                        <div>
                            <input id="branding-logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="ui-input h-auto py-2" aria-describedby="branding-logo-help branding-logo-file-error">
                            <p id="branding-logo-file" class="mt-2 text-xs text-[#78909a]" data-branding-file-name>Belum ada file baru yang dipilih.</p>
                            @error('logo')
                                <p id="branding-logo-file-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                            @if ($branding['logo_path'])
                                <label class="mt-3 inline-flex min-h-10 items-center gap-2 rounded-lg border border-[#dce9ed] bg-[#f8fbfc] px-3 text-xs font-bold text-[#526f79]">
                                    <input type="checkbox" name="remove_logo" value="1" class="ui-checkbox" @checked(old('remove_logo'))>
                                    Hapus logo aktif setelah disimpan
                                </label>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#e7eef1] pt-5">
                    <p class="max-w-xl text-xs leading-5 text-[#78909a]">Perubahan dicatat dengan pelaku, waktu, versi, serta nilai sebelum dan sesudah pada audit log.</p>
                    <button type="submit" class="ui-btn ui-btn-primary" data-branding-submit>
                        <span data-branding-submit-label>Simpan identitas</span>
                        <span class="hidden" data-branding-submit-loading aria-hidden="true">Menyimpan…</span>
                    </button>
                </div>
            </form>
        </section>

        <aside class="ui-panel overflow-hidden" aria-labelledby="branding-preview-heading">
            <div class="ui-panel-header">
                <div>
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#ffd44f] !shadow-[0_0_0_4px_#fff4cf]" aria-hidden="true"></span>Pratinjau</p>
                    <h2 id="branding-preview-heading" class="ui-section-title">Wajah portal</h2>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div class="overflow-hidden rounded-xl border border-[#d9e5e9] bg-white shadow-[0_14px_32px_rgba(38,58,67,0.1)]">
                    <div class="flex min-h-44">
                        <div class="w-2 shrink-0 bg-[#ffd438]" aria-hidden="true"></div>
                        <div class="flex min-w-0 flex-1 flex-col p-5 sm:p-6">
                            <div class="flex items-center gap-3">
                                @if ($branding['logo_url'])
                                    <img src="{{ $branding['logo_url'] }}" alt="" class="h-12 max-w-32 object-contain" loading="lazy">
                                @else
                                    <span class="ui-brand-mark !h-12 !w-12 !rounded-lg text-sm">{{ $branding['monogram'] }}</span>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-extrabold tracking-[-0.03em] text-[#18252b]">{{ $branding['application_name'] }}</p>
                                    <p class="mt-1 truncate text-[0.68rem] text-[#829198]">{{ $branding['organization_name'] }}</p>
                                </div>
                            </div>
                            <div class="mt-8">
                                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#8b9a9f]">Selamat datang</p>
                                <p class="mt-2 text-lg font-semibold tracking-tight text-[#3f4548]">{{ $branding['tagline'] ?: 'Portal layanan internal' }}</p>
                            </div>
                            <div class="mt-auto border-t border-[#edf2f4] pt-4 text-[0.68rem] leading-5 text-[#8a999e]">
                                {{ $branding['footer_text'] ?: 'Portal layanan TI internal' }}
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mt-4 text-xs leading-5 text-[#78909a]">Pratinjau ini mengikuti struktur halaman masuk dan identitas utama ruang kerja.</p>
            </div>
        </aside>
    </div>

    <section class="ui-panel mt-5 overflow-hidden" aria-labelledby="branding-history-heading">
        <div class="ui-panel-header">
            <div>
                <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#6ab8d7] !shadow-[0_0_0_4px_#e2f5fb]" aria-hidden="true"></span>Jejak perubahan</p>
                <h2 id="branding-history-heading" class="ui-section-title">Riwayat identitas</h2>
                <p class="ui-section-description">Setiap versi tetap disimpan agar perubahan branding dapat ditelusuri dan dicocokkan dengan audit log.</p>
            </div>
            <a href="{{ route('admin.audit-logs.index', ['action' => 'admin.branding.updated']) }}" class="ui-action-link">Buka audit log <span aria-hidden="true">→</span></a>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="ui-table w-full text-left text-sm">
                <caption class="sr-only">Daftar versi identitas aplikasi</caption>
                <thead class="bg-[#f8fbfc] text-[0.68rem] uppercase tracking-[0.1em] text-[#78909a]">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-extrabold">Versi</th>
                        <th scope="col" class="px-5 py-3 font-extrabold">Nama aplikasi</th>
                        <th scope="col" class="px-5 py-3 font-extrabold">Nama instansi</th>
                        <th scope="col" class="px-5 py-3 font-extrabold">Diubah oleh</th>
                        <th scope="col" class="px-5 py-3 font-extrabold">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($versions as $version)
                        <tr>
                            <td class="whitespace-nowrap font-extrabold text-[#35505b]">v{{ $version->version }} @if ($version->is_active)<span class="ui-status ui-status-active ml-1">Aktif</span>@endif</td>
                            <td class="text-[#526b75]">{{ $version->application_name }}</td>
                            <td class="text-[#526b75]">{{ $version->organization_name }}</td>
                            <td class="text-[#526b75]">{{ $version->changedBy?->name ?? 'Default sistem' }}</td>
                            <td class="whitespace-nowrap text-[#78909a]">{{ $version->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="ui-empty m-4">Belum ada versi yang disimpan. Nilai default aman masih digunakan.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-[#edf2f4] md:hidden">
            @forelse ($versions as $version)
                <article class="space-y-2 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <p class="font-extrabold text-[#35505b]">Versi {{ $version->version }}</p>
                        @if ($version->is_active)<span class="ui-status ui-status-active">Aktif</span>@endif
                    </div>
                    <p class="text-sm text-[#526b75]">{{ $version->application_name }} · {{ $version->organization_name }}</p>
                    <p class="text-xs text-[#78909a]">{{ $version->changedBy?->name ?? 'Default sistem' }} · {{ $version->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</p>
                </article>
            @empty
                <div class="ui-empty m-4">Belum ada versi yang disimpan. Nilai default aman masih digunakan.</div>
            @endforelse
        </div>
    </section>
@endsection
