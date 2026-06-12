@extends('layouts.app')

@section('title', 'Modifier production')

@section('content')

    <a href="{{ route('productions.index') }}" class="back-link">← Productions</a>

    <div class="card">
        <div class="card-title">Production du {{ $production->date_production->format('d/m/Y') }}</div>
        <form method="POST" action="{{ route('productions.update', $production) }}">
            @csrf
            @method('PUT')
            @include('productions._form', ['production' => $production])
        </form>
    </div>

@endsection
