@extends('layouts.app')

@section('title', 'Tambah Pengguna — '.$branding['application_name'])
@section('header_kicker', 'Manajemen Pengguna')
@section('header_title', 'Tambah pengguna')

@section('content')
    <div class="max-w-3xl">
        <x-page-header
            eyebrow="Data Master · Akses pengguna"
            title="Tambah Pengguna"
            description="Isi data akun dan pilih role pengguna."
            :back-url="route('admin.users.index')"
            back-label="Kembali ke daftar pengguna"
        />
        <div class="ui-panel mt-7 overflow-hidden">
            <div class="border-b border-[#075998] bg-[#075998] px-5 py-4 text-white sm:px-6">
                <h2 class="text-lg font-extrabold">Data akun</h2>
            </div>
            @include('admin.users._form', ['action' => route('admin.users.store'), 'method' => 'POST', 'formId' => 'create-page', 'prefix' => 'user-create-page', 'isEdit' => false, 'isModal' => false])
        </div>
    </div>
@endsection
