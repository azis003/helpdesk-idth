@extends('layouts.app')

@section('title', 'Ganti Password — '.$branding['application_name'])
@section('header_title', 'Ganti Password')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Keamanan akun</p>
            <h1 class="ui-page-title">Ganti Password</h1>
            <p class="ui-page-description">Buat password baru untuk menjaga keamanan akun Anda.</p>
        </div>
    </div>

    <section class="ui-panel mt-8 max-w-2xl overflow-hidden" aria-labelledby="change-password-heading">
        <div class="ui-panel-header">
            <h2 id="change-password-heading" class="ui-section-title">Password baru</h2>
            <p class="ui-section-description">Gunakan password yang hanya Anda ketahui.</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="p-5 sm:p-6" data-submit-feedback>
            @csrf
            @method('PUT')

            <div class="grid gap-4">
                <div>
                    <label for="current_password" class="ui-field-label">Password saat ini <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="ui-input mt-2" @error('current_password') aria-invalid="true" aria-describedby="current-password-error" @enderror>
                    @error('current_password')<p id="current-password-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="ui-field-label">Password baru <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required aria-describedby="password-help @error('password') password-error @enderror" class="ui-input mt-2" @error('password') aria-invalid="true" @enderror>
                    <p id="password-help" class="ui-field-help">Minimal 12 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.</p>
                    @error('password')<p id="password-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="ui-field-label">Konfirmasi password baru <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="ui-input mt-2">
                </div>
            </div>

            <div class="mt-5 flex flex-col-reverse gap-3 border-t border-[#edf2f4] pt-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs leading-5 text-[#6f858e]">Jangan bagikan password Anda kepada siapa pun.</p>
                <button type="submit" class="ui-btn ui-btn-primary" data-submit-button>
                    <span data-submit-label>Simpan password baru</span>
                    <span data-submit-loading class="hidden">Menyimpan...</span>
                </button>
            </div>
        </form>
    </section>
@endsection
