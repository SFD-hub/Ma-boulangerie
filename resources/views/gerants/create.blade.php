@extends('layouts.app')

@section('title', 'Nouveau gérant')

@section('content')

    <a href="{{ route('gerants.index') }}" class="back-link">← Gérants</a>

    <div class="card">
        <div class="card-title">Nouveau gérant</div>
        <form method="POST" action="{{ route('gerants.store') }}">
            @csrf
            @include('gerants._form')
        </form>
    </div>

@endsection
