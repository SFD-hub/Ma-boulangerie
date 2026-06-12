@extends('layouts.app')

@section('title', 'Nouvelle production')

@section('content')

    <a href="{{ route('productions.index') }}" class="back-link">← Productions</a>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-title">Nouvelle production</div>
        <form method="POST" action="{{ route('productions.store') }}">
            @csrf
            @include('productions._form')
        </form>
    </div>

@endsection
