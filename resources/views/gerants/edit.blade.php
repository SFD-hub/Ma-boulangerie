@extends('layouts.app')

@section('title', 'Modifier gérant')

@section('content')

    <a href="{{ route('gerants.index') }}" class="back-link">← Gérants</a>

    <div class="card">
        <div class="card-title">{{ $gerant->name }}</div>
        <form method="POST" action="{{ route('gerants.update', $gerant) }}">
            @csrf
            @method('PUT')
            @include('gerants._form', ['gerant' => $gerant])
        </form>
    </div>

@endsection
