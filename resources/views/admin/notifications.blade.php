@extends('layouts.dashboard', ['role' => 'admin'])
@section('title', 'Notifications')
@section('content')
@include('partials.dash.notifications', ['items' => $items])
@endsection
