@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')

<div class="grid grid-cols-1 gap-6">
    <div class="bg-gradient-to-r from-blue-500 to-blue-400 rounded-lg p-8 text-white shadow-md">
        <h1 class="text-3xl font-bold mb-2 text-primary">Selamat datang, {{ session('nama') }}!</h1>
    </div>
</div>

@endsection