@extends('layouts.dashboard', ['role' => 'manager'])
@section('title', 'Notifications')
@section('content')
@include('partials.dash.notifications', ['items' => $items])
@endsection
