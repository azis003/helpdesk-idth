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
        <div class="ui-panel mt-6 overflow-hidden">
            <div class="flex items-center gap-3 border-b border-[color:var(--tm-border-subtle)] px-5 py-4 sm:px-6">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-700)]" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.5 20v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1" /><circle cx="8.75" cy="7.5" r="3.5" /><path stroke-linecap="round" d="M18 8.5v6M15 11.5h6" /></svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold tracking-tight text-[color:var(--tm-text)]">Data akun</h2>
                    <p class="mt-0.5 text-xs text-[color:var(--tm-text-muted)]">Lengkapi identitas, tim kerja, dan role pengguna baru.</p>
                </div>
            </div>
            @include('admin.users._form', ['action' => route('admin.users.store'), 'method' => 'POST', 'formId' => 'create-page', 'prefix' => 'user-create-page', 'isEdit' => false, 'isModal' => false])
        </div>
    </div>
@endsection
