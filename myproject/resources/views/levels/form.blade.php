@extends('layouts.app', ['title' => $level->exists ? 'Edit level' : 'Add level'])

@section('content')
<section class="page-heading"><div><p class="eyebrow">Configuration · Levels</p><h1>{{ $level->exists ? 'Edit level' : 'Add a membership level' }}</h1><p class="page-subtitle">Define the value members receive at this level.</p></div><a class="secondary-button" href="{{ route('levels.index') }}">← Back to levels</a></section>
<section class="form-panel narrow-panel"><form class="stack-form" action="{{ $level->exists ? route('levels.update', $level) : route('levels.store') }}" method="POST">@csrf @if ($level->exists) @method('PUT') @endif
    @if ($errors->any())<div class="error-box">Please correct the highlighted fields.</div>@endif
    <div class="form-grid"><div><label for="name">Level name</label><input id="name" name="name" value="{{ old('name', $level->name) }}" placeholder="e.g. Gold" required>@error('name')<p class="form-error">{{ $message }}</p>@enderror</div><div><label for="fee">Membership fee</label><div class="input-prefix"><span>₱</span><input id="fee" name="fee" type="number" step="0.01" min="0" value="{{ old('fee', $level->fee ?? 0) }}" required></div>@error('fee')<p class="form-error">{{ $message }}</p>@enderror</div></div>
    <div><label for="validity_months">Validity period</label><div class="input-suffix"><input id="validity_months" name="validity_months" type="number" min="1" max="120" value="{{ old('validity_months', $level->validity_months ?? 12) }}" required><span>months</span></div></div>
    <div><label for="benefits">Benefits</label><textarea id="benefits" name="benefits" rows="5" placeholder="Describe what members receive at this level.">{{ old('benefits', $level->benefits) }}</textarea>@error('benefits')<p class="form-error">{{ $message }}</p>@enderror</div>
    <div class="form-actions"><a class="secondary-button" href="{{ route('levels.index') }}">Cancel</a><button class="primary-button" type="submit">{{ $level->exists ? 'Save level' : 'Create level' }}</button></div>
</form></section>
@endsection
