@extends('layouts.app')

@section('title', 'Créer une matière première')

@section('header')
    <h1>Créer une matière première</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('matieres-premieres.store') }}">
        @csrf
        @include('matieres-premieres._form')
    </form>
@endsection
