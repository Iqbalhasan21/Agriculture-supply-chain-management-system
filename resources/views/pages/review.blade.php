@extends('layouts.public')
@section('title', 'Write a Review')
@section('content')
<div class="container section" style="max-width:660px">
    <form class="card" method="POST" action="{{ route('review.store') }}">
        @csrf
        <input type="hidden" name="product_id" value="{{ $p['id'] }}">
        <h1 style="font-size:26px;margin-bottom:16px">Write a Review</h1>
        @include('partials.form-errors')
        <div class="row gap-16" style="justify-content:flex-start;flex-wrap:nowrap;background:var(--bg);padding:14px;border-radius:10px;margin-bottom:18px">
            @if ($p['image'])<img class="thumb-sm" style="width:64px;height:64px" src="{{ asset('images/' . $p['image']) }}" alt="">@else<span class="thumb-sm" style="width:64px;height:64px">{{ $p['emoji'] }}</span>@endif
            <div><div class="bold">{{ $p['name'] }}</div><div class="muted">{{ $p['farmer'] }} · {{ $p['district'] }}</div></div>
        </div>
        <fieldset class="form-group" style="border:0;padding:0;margin:0 0 14px">
            <legend class="bold" style="font-size:13px;margin-bottom:6px">Your Rating</legend>
            <div class="row" style="justify-content:flex-start;gap:14px">
                @for ($i = 5; $i >= 1; $i--)
                    <label class="check"><input type="radio" name="rating" value="{{ $i }}" @checked((int) old('rating', 5) === $i) required> {{ $i }} ★</label>
                @endfor
            </div>
        </fieldset>
        <div class="form-group"><label for="title">Review Title</label><input id="title" name="title" value="{{ old('title') }}" placeholder="Summarize your experience"></div>
        <div class="form-group"><label for="body">Your Review</label><textarea id="body" name="body" required placeholder="Tell others what you liked or disliked...">{{ old('body') }}</textarea></div>
        <div class="row gap-12" style="justify-content:flex-start"><button class="btn" type="submit">Submit Review</button><a class="btn btn-outline" href="{{ route('product.show', $p['id']) }}">Cancel</a></div>
    </form>
</div>
@endsection
