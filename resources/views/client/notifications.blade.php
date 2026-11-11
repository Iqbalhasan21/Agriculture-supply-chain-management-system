@extends('layouts.dashboard', ['role' => 'client'])
@section('title', 'Notifications')
@section('content')
@include('partials.dash.notifications', ['items' => $items])
@endsection
