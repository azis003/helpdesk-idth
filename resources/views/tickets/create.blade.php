@extends('layouts.app')

@php($isFormStep = $selectedServiceType !== null)

@section('title', ($isFormStep ? 'Formulir tiket' : 'Pilih layanan').' — '.$branding['application_name'])
@section('header_kicker', 'Tiket')
@section('header_title', $isFormStep ? 'Formulir tiket' : 'Pilih layanan')

@section('content')
    @if ($isFormStep)
        @include('tickets._form')
    @else
        @include('tickets._catalog')
    @endif
@endsection
