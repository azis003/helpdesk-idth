@extends('layouts.app')

@section('title', 'Edit Profil — '.$branding['application_name'])
@section('header_title', 'Edit Profil')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Akun saya</p>
            <h1 class="ui-page-title">Edit Profil</h1>
            <p class="ui-page-description">Perbarui informasi kontak yang digunakan tim untuk mengenali dan menghubungi Anda.</p>
        </div>
    </div>

    <div class="mt-8 max-w-3xl">
        <section class="ui-panel overflow-hidden" aria-labelledby="profile-information-heading">
            <div class="ui-panel-header">
                <h2 id="profile-information-heading" class="ui-section-title">Informasi profil</h2>
                <p class="ui-section-description">Data ini digunakan agar tim dapat mengenali dan menghubungi Anda.</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="p-5 sm:p-6" data-submit-feedback>
                @csrf
                @method('PATCH')

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="profile-name" class="ui-field-label">Nama pengguna <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                        <input id="profile-name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required class="ui-input mt-2" @error('name') aria-invalid="true" aria-describedby="profile-name-error" @enderror>
                        @error('name')<p id="profile-name-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="profile-email" class="ui-field-label">Email</label>
                        <input id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" class="ui-input mt-2" @error('email') aria-invalid="true" aria-describedby="profile-email-error" @enderror>
                        @error('email')<p id="profile-email-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="profile-username" class="ui-field-label">Username</label>
                        <input id="profile-username" type="text" value="{{ $user->username }}" readonly aria-describedby="profile-username-help" class="ui-input mt-2 cursor-not-allowed bg-[#f6f9fa] text-[#617780]">
                        <p id="profile-username-help" class="ui-field-help">Username dikelola oleh administrator.</p>
                    </div>

                    <div>
                        <label for="profile-nip" class="ui-field-label">NIP</label>
                        <input id="profile-nip" type="text" value="{{ $user->nip ?? '—' }}" readonly class="ui-input mt-2 cursor-not-allowed bg-[#f6f9fa] text-[#617780]">
                    </div>
                </div>

                <div class="mt-5 flex justify-end border-t border-[#edf2f4] pt-4">
                    <button type="submit" class="ui-btn ui-btn-primary" data-submit-button>
                        <span data-submit-label>Simpan profil</span>
                        <span data-submit-loading class="hidden">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
