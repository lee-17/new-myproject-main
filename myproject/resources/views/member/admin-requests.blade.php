@extends('layouts.app', ['title' => 'Request History'])

@section('content')
<section class="page-heading"><div><p class="eyebrow">Member portal</p><h1>Request history</h1><p class="page-subtitle">Review decisions on your administrator-access requests.</p></div><a class="primary-button" href="{{ route('member.administrator-request.create') }}">＋ New request</a></section>
<section class="panel table-panel"><div class="data-table-wrap"><table class="data-table"><thead><tr><th>Submitted</th><th>Reason</th><th>Status</th><th>Decision</th></tr></thead><tbody>@forelse ($requests as $request)<tr><td>{{ $request->created_at->format('M j, Y') }}</td><td>{{ $request->reason }}</td><td><span class="status-pill status-{{ $request->status }}">{{ ucfirst($request->status) }}</span></td><td>{{ $request->decision_message ?: 'Awaiting review' }}</td></tr>@empty<tr><td colspan="4" class="empty-cell">No administrator requests yet.</td></tr>@endforelse</tbody></table></div></section>
@endsection
