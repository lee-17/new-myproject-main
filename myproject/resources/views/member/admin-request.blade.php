@extends('layouts.app', ['title' => 'Administrator Registration'])

@section('content')
<section class="page-heading"><div><p class="eyebrow">Member portal</p><h1>Administrator registration</h1><p class="page-subtitle">Tell the membership team why you need administrator access.</p></div><a class="secondary-button" href="{{ route('member.admin-requests') }}">Request history</a></section>
<section class="form-panel narrow-panel"><form class="stack-form" action="{{ route('member.administrator-request.store') }}" method="POST">@csrf@if ($errors->any())<div class="error-box">{{ $errors->first() }}</div>@endif<div><label for="reason">Reason for administrator access</label><textarea id="reason" name="reason" rows="7" placeholder="Explain how you will help manage the membership system." required>{{ old('reason') }}</textarea></div><div class="form-note"><span class="form-note-icon">i</span><span>Your request will be reviewed by an existing administrator.</span></div><div class="form-actions"><a class="secondary-button" href="{{ route('member.dashboard') }}">Cancel</a><button class="primary-button" type="submit">Submit administrator request</button></div></form></section>
@endsection
