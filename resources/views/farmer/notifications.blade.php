@extends('layouts.dashboard', ['role' => 'farmer'])
@section('title', 'Notifications')
@section('content')
@include('partials.dash.notifications', ['items' => $items])
@endsection
