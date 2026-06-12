@extends('layouts.app')

@section('title', 'Éditer une matière première')

@section('header')
    <h1>Éditer "{{ $matierePremiere->nom }}"</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('matieres-premieres.update', $matierePremiere) }}">
        @csrf
        @method('PUT')
        @include('matieres-premieres._form', ['matierePremiere' => $matierePremiere])
    </form>
@endsection
