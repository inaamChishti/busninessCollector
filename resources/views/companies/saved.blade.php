@extends('layouts.app')
@section('title','Companies Businesses — Saved Leads')
@section('subtitle','All registered companies saved from Companies Businesses searches')
@section('content')

<style>
.ch-wrap{overflow-x:auto}
.ch-table{width:100%;border-collapse:collapse;font-size:.82rem}
.ch-table th{background:var(--surface,#f8f9fa);padding:.55rem .9rem;text-align:left;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted,#888);border-bottom:2px solid var(--border,#e5e7eb);white-space:nowrap}
.ch-table td{padding:.55rem .9rem;border-bottom:1px solid var(--border,#f3f4f6);vertical-align:middle}
.ch-table tr:hover td{background:#fafafa}
.status-active{color:#16a34a;font-weight:600;font-size:.75rem}
.status-dissolved{color:#dc2626;font-size:.75rem}
.status-other{color:#92400e;font-size:.75rem}
.ch-num{color:var(--text-muted,#aaa);font-size:.72rem}
.ch-badge{padding:.2rem .55rem;border-radius:999px;font-size:.75rem;font-weight:600;color:#fff;background:var(--primary,#3b5bdb)}
.filter-bar{display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1.25rem}
.filter-bar .field{display:flex;flex-direction:column;gap:.25rem}
.filter-bar label{font-size:.72rem;font-weight:600;color:var(--text-muted,#666);text-transform:uppercase;letter-spacing:.04em}
.filter-bar select,.filter-bar input{padding:.45rem .7rem;border:1px solid var(--border,#ddd);border-radius:6px;font-size:.875rem;background:#fff}
.stat-row{display:flex;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap}
.stat-card{background:var(--surface,#f8f9fa);border:1px solid var(--border,#e5e7eb);border-radius:8px;padding:.75rem 1.25rem;min-width:140px}
.stat-card strong{display:block;font-size:1.4rem;font-weight:700}
.stat-card span{font-size:.75rem;color:var(--text-muted,#888)}
</style>

{{-- Stats --}}
<div class="stat-row">
    <div class="stat-card"><strong>{{ number_format($stats['total']) }}</strong><span>Total Saved</span></div>
    <div class="stat-card"><strong style="color:#16a34a">{{ number_format($stats['active']) }}</strong><span>Active Companies</span></div>
    <div class="stat-card"><strong style="color:var(--primary,#3b5bdb)">{{ number_format($stats['with_email']) }}</strong><span>With Email</span></div>
</div>

{{-- Filters --}}
<div class="panel" style="margin-bottom:1rem">
    <div style="padding:1.25rem">
        <form method="GET" action="{{ route('companies.saved') }}">
            <div class="filter-bar">
                <div class="field">
                    <label>Category</label>
                    <select name="category">
                        <option value="">All categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All statuses</option>
                        @foreach(['active','dissolved','liquidation','administration'] as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Postcode</label>
                    <input type="text" name="postcode" value="{{ request('postcode') }}" placeholder="e.g. IG11" style="width:110px">
                </div>
                <div class="field">
                    <label>Company Name</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name…">
                </div>
                <div class="field" style="justify-content:flex-end">
                    <label style="visibility:hidden">x</label>
                    <div style="display:flex;gap:.5rem">
                        <button class="btn primary" type="submit">Filter</button>
                        <a class="btn" href="{{ route('companies.saved') }}">Clear</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Saved Leads <span class="ch-badge">{{ $leads->total() }}</span></h2>
        <a href="{{ route('companies.index') }}" class="btn">← New Search</a>
    </div>
    <div style="padding:1.25rem">

        @if($leads->isEmpty())
            <p style="text-align:center;padding:3rem;color:var(--text-muted,#888)">
                No leads saved yet. <a href="{{ route('companies.index') }}">Run a search first.</a>
            </p>
        @else
        <div class="ch-wrap">
            <table class="ch-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Company Name</th>
                        <th>Company No.</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Address</th>
                        <th>Postcode</th>
                        <th>Business Activity</th>
                        <th>Incorporated</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $lead)
                    <tr>
                        <td class="ch-num">{{ $loop->iteration + ($leads->currentPage()-1)*$leads->perPage() }}</td>
                        <td>
                            <strong>{{ $lead->company_name }}</strong>
                            @if($lead->company_type)<br><span class="ch-num">{{ $lead->company_type }}</span>@endif
                        </td>
                        <td class="ch-num">{{ $lead->company_number }}</td>
                        <td>
                            @php
                                $cls = match($lead->company_status){
                                    'active'     => 'status-active',
                                    'dissolved'  => 'status-dissolved',
                                    default      => 'status-other'
                                };
                            @endphp
                            <span class="{{ $cls }}">{{ $lead->company_status ?? '—' }}</span>
                        </td>
                        <td class="ch-num">{{ $lead->category ?? '—' }}</td>
                        <td>
                            {{ collect([$lead->address_line_1,$lead->address_line_2,$lead->locality])->filter()->implode(', ') ?: '—' }}
                        </td>
                        <td class="ch-num">{{ $lead->postal_code ?? '—' }}</td>
                        <td style="font-size:.75rem;max-width:180px">
                            @php
                                $sicDecoded = $lead->sic_codes
                                    ? collect(explode(',', $lead->sic_codes))
                                        ->map(fn($c) => \App\Services\CompaniesHouseService::sicDescription(trim($c)))
                                        ->unique()->implode(' · ')
                                    : '—';
                            @endphp
                            {{ $sicDecoded }}
                        </td>
                        <td class="ch-num">{{ $lead->date_of_creation?->format('d M Y') ?? '—' }}</td>
                        <td class="ch-num">
                            @if($lead->email)
                                <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                            @else
                                <span style="color:#d1d5db">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
        <div style="margin-top:1rem">{{ $leads->links() }}</div>
        @endif
        @endif
    </div>
</div>

@endsection
