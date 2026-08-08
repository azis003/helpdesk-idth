@extends('layouts.app')

@section('title', 'Tambah Pengguna — '.$branding['application_name'])
@section('header_kicker', 'Manajemen Pengguna')
@section('header_title', 'Tambah pengguna')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('admin.users.index') }}" class="ui-action-link">← Kembali ke daftar pengguna</a>
        <div class="ui-panel mt-5 overflow-hidden">
            <div class="border-b border-[#e7eef1] bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                <h1 class="text-lg font-extrabold">Tambah Pengguna</h1>
                <p class="mt-1 text-xs text-white/80">Isi data akun dan pilih role pengguna.</p>
            </div>
            @include('admin.users._form', ['action' => route('admin.users.store'), 'method' => 'POST', 'formId' => 'create-page', 'prefix' => 'user-create-page', 'isEdit' => false, 'isModal' => false])
        </div>
    </div>
@endsection
