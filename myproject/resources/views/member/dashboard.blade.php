@extends('layouts.app', ['title' => 'My Dashboard'])

@section('content')
<section class="page-heading dashboard-heading"><div><p class="eyebrow">Member portal</p><h1>Welcome, <span>{{ $member->first_name }}</span></h1><p class="page-subtitle">Track your membership, assigned tasks, and transaction requests in one place.</p></div><a class="primary-button" href="{{ route('member.profile') }}">View my profile</a></section>

<section class="metric-grid compact-metrics">
    <article class="metric-card metric-blue"><p>Membership status</p><strong>{{ ucfirst($member->status) }}</strong><span>{{ $member->level }} · {{ $member->category }}</span></article>
    <article class="metric-card metric-purple"><p>Valid until</p><strong>{{ $member->membership_expires_at?->format('M j') ?? 'Pending' }}</strong><span>{{ $member->membership_expires_at?->format('Y') ?? 'Awaiting approval' }}</span></article>
    <article class="metric-card metric-sky"><p>Open tasks</p><strong>{{ $member->tasks()->where('status', 'pending')->count() }}</strong><span>Assigned activities</span></article>
    <article class="metric-card metric-lilac"><p>Transactions</p><strong>{{ $member->transactions()->count() }}</strong><span>Membership requests</span></article>
</section>

<div class="dashboard-grid">
    <section class="panel"><div class="panel-heading"><div><p class="eyebrow">Membership activity</p><h2>Recent transactions</h2></div><a class="text-link" href="{{ route('member.transactions') }}">View all →</a></div><div class="transaction-list">
        @forelse ($transactions as $transaction)
            <a class="transaction-row" href="{{ route('member.transactions') }}"><span class="transaction-symbol symbol-{{ $transaction->status }}">{{ strtoupper(substr($transaction->type, 0, 1)) }}</span><span class="transaction-main"><strong>{{ ucfirst($transaction->type) }} · {{ $transaction->transaction_number }}</strong><small>{{ $transaction->level?->name ?? 'Membership level' }} · {{ $transaction->created_at->format('M j, Y') }}</small></span><span class="transaction-amount">₱{{ number_format((float) $transaction->amount, 2) }}<small class="status-text status-{{ $transaction->status }}">{{ ucfirst($transaction->status) }}</small></span></a>
        @empty
            <div class="empty-state compact"><span class="empty-icon">↗</span><h3>No transactions yet</h3><p>Your membership requests will appear here.</p></div>
        @endforelse
    </div></section>

    <section class="panel"><div class="panel-heading"><div><p class="eyebrow">Learning tasks</p><h2>My tasks</h2></div><a class="text-link" href="{{ route('member.tasks') }}">Open tasks →</a></div><div class="member-mini-list">
        @forelse ($tasks as $task)
            <a class="member-mini" href="{{ route('member.tasks') }}"><span class="avatar avatar-{{ $loop->index % 4 }}">{{ $task->status === 'completed' ? '✓' : '!' }}</span><span><strong>{{ $task->task_title }}</strong><small>{{ $task->status === 'completed' ? 'Completed' : 'Pending' }}</small></span><span class="mini-arrow">→</span></a>
        @empty
            <div class="empty-state compact"><span class="empty-icon">✦</span><h3>No tasks assigned</h3><p>Assigned activities will appear here.</p></div>
        @endforelse
    </div></section>
</div>
@endsection
