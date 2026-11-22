@extends('layouts.app', ['title' => $category->exists ? 'Edit category' : 'Add category'])

@section('content')
<section class="page-heading"><div><p class="eyebrow">Configuration · Categories</p><h1>{{ $category->exists ? 'Edit category' : 'Add a category' }}</h1><p class="page-subtitle">Keep category labels clear for members and staff.</p></div><a class="secondary-button" href="{{ route('categories.index') }}">← Back to categories</a></section>
<section class="form-panel narrow-panel"><form class="stack-form" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" method="POST">@csrf @if ($category->exists) @method('PUT') @endif
    @if ($errors->any())<div class="error-box">Please correct the highlighted fields.</div>@endif
    <div class="form-grid"><div><label for="name">Category name</label><input id="name" name="name" value="{{ old('name', $category->name) }}" placeholder="e.g. Corporate" required>@error('name')<p class="form-error">{{ $message }}</p>@enderror</div><div><label for="level">Audience label</label><input id="level" name="level" value="{{ old('level', $category->level) }}" placeholder="e.g. Business"></div></div>
    <div class="form-grid"><div><label for="fee">Base fee</label><div class="input-prefix"><span>₱</span><input id="fee" name="fee" type="number" step="0.01" min="0" value="{{ old('fee', $category->fee ?? 0) }}" required></div>@error('fee')<p class="form-error">{{ $message }}</p>@enderror</div><div class="toggle-field"><label for="is_active">Availability</label><label class="checkbox-label"><input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $category->exists ? $category->is_active : true))> Available for applications</label></div></div>
    <div class="form-actions"><a class="secondary-button" href="{{ route('categories.index') }}">Cancel</a><button class="primary-button" type="submit">{{ $category->exists ? 'Save category' : 'Create category' }}</button></div>
</form></section>
@endsection
