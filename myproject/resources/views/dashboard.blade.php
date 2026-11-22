@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<section class="page-heading dashboard-heading">
    <div><p class="eyebrow">Sunday, {{ now()->format('F j, Y') }}</p><h1>Good morning, <span>administrator.</span></h1><p class="page-subtitle">Here is the pulse of your membership community today.</p></div>
    <a class="primary-button" href="{{ route('transactions.create') }}">＋ Create transaction</a>
</section>

<section class="metric-grid" aria-label="Membership overview">
    <article class="metric-card metric-blue"><div class="metric-icon">♙</div><p>Total members</p><strong>{{ number_format($stats['members']) }}</strong><span>Registered profiles</span></article>
    <article class="metric-card metric-purple"><div class="metric-icon">✦</div><p>Active members</p><strong>{{ number_format($stats['active_members']) }}</strong><span>Currently in good standing</span></article>
    <article class="metric-card metric-sky"><div class="metric-icon">↗</div><p>Pending transactions</p><strong>{{ number_format($stats['pending_transactions']) }}</strong><span>Waiting for payment</span></article>
    <article class="metric-card metric-lilac"><div class="metric-icon">₱</div><p>Collected payments</p><strong>₱{{ number_format((float) $stats['collected_payments'], 2) }}</strong><span>All recorded payments</span></article>
</section>

<section class="dashboard-grid">
    <article class="panel panel-wide">
        <div class="panel-heading"><div><p class="eyebrow">Operations stream</p><h2>Recent transactions</h2></div><a class="text-link" href="{{ route('transactions.index') }}">View all →</a></div>
        <div class="transaction-list">
            @forelse ($recentTransactions as $transaction)
                <a class="transaction-row" href="{{ auth()->check() ? route('transactions.show', $transaction) : route('login') }}">
                    <span class="transaction-symbol symbol-{{ $transaction->status }}">{{ $transaction->type === 'renewal' ? '↻' : '＋' }}</span>
                    <span class="transaction-main"><strong>{{ $transaction->member->full_name }}</strong><small>{{ ucfirst($transaction->type) }} · {{ $transaction->transaction_number }}</small></span>
                    <span class="transaction-amount">₱{{ number_format((float) $transaction->amount, 2) }}<small class="status-text status-{{ $transaction->status }}">{{ ucfirst($transaction->status) }}</small></span>
                </a>
            @empty
                <div class="empty-state"><span class="empty-icon">✦</span><h3>No transactions yet</h3><p>Start by creating an application or renewal transaction.</p><a class="text-link" href="{{ route('transactions.create') }}">Create the first transaction →</a></div>
            @endforelse
        </div>
    </article>

    <article class="panel panel-side">
        <div class="panel-heading"><div><p class="eyebrow">Community</p><h2>New members</h2></div><a class="text-link" href="{{ route('members.index') }}">Directory →</a></div>
        <div class="member-mini-list">
            @forelse ($recentMembers as $member)
                <a class="member-mini" href="{{ route('members.show', $member) }}"><span class="avatar avatar-{{ $loop->index % 4 }}">{{ strtoupper(substr($member->first_name, 0, 1).substr($member->last_name, 0, 1)) }}</span><span><strong>{{ $member->full_name }}</strong><small>{{ $member->membership_number ?? 'Membership number pending' }}</small></span><span class="mini-arrow">→</span></a>
            @empty
                <div class="empty-state compact"><span class="empty-icon">♙</span><p>No member profiles have been added.</p><a class="text-link" href="{{ route('members.create') }}">Add a member →</a></div>
            @endforelse
        </div>
    </article>
</section>

<section class="quick-actions">
    <div><p class="eyebrow">Quick actions</p><h2>Move the work forward</h2></div>
    <div class="quick-action-grid"><a href="{{ route('members.create') }}"><span>♙</span><strong>Register member</strong><small>Create a new profile</small><b>→</b></a><a href="{{ route('transactions.create') }}"><span>↗</span><strong>Record transaction</strong><small>Application or renewal</small><b>→</b></a><a href="{{ route('categories.index') }}"><span>◈</span><strong>Manage setup</strong><small>Categories and levels</small><b>→</b></a></div>
</section>
@endsection
