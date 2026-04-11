@extends('layouts.app')

@section('title', 'Dashboard Pelanggan')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Selamat Datang, {{ auth()->user()->nama }}</h4>
                <p class="card-description">Ini adalah halaman dashboard untuk pelanggan.</p>
                <p>Role: Pelanggan (idrole: {{ auth()->user()->idrole }})</p>
            </div>
        </div>
    </div>
</div>
@endsection
