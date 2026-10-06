@extends('layouts.app')
@section('title','Wikidata — Saved Businesses')
@section('subtitle','All businesses stored from Wikidata API searches')
@section('content')

<style>
.wd-table-wrap{overflow-x:auto}
.wd-table{width:100%;border-collapse:collapse;font-size:.875rem}
.wd-table th{background:var(--surface,#f8f9fa);padding:.6rem 1rem;text-align:left;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted,#888);border-bottom:2px solid var(--border,#e5e7eb);white-space:nowrap}
.wd-table td{padding:.6rem 1rem;border-bottom:1px solid var(--border,#f0f0f0);vertical-align:middle}
.wd-table tr:hover td{background:var(--surface,#fafafa)}
.wd-table td a{color:var(--primary,#3b5bdb);text-decoration:none;font-size:.8rem;word-break:break-all}
.wd-num{color:var(--text-muted,#aaa);font-size:.75rem}
.wd-badge{background:var(--primary,#3b5bdb);color:#fff;padding:.2rem .55rem;border-radius:999px;font-size:.75rem;font-weight:600}
.filter-bar{display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1.25rem}
.filter-bar .field{display:flex;flex-direction:column;gap:.25rem}
.filter-bar label{font-size:.75rem;font-weight:600;color:var(--text-muted,#666);text-transform:uppercase;letter-spacing:.04em}
.filter-bar select,.filter-bar input{padding:.45rem .7rem;border:1px solid var(--border,#ddd);border-radius:6px;font-size:.875rem;background:#fff}
</style>

<div class="panel" style="margin-bottom:1rem">
    <div style="padding:1.25rem">
        <form method="GET" action="{{ route('wikidata.saved') }}">
            <div class="filter-bar">
                <div class="field">
                    <label>City</label>
                    <select name="city">
                        <option value="">All cities</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" @selected(request('city')===$c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
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
                    <label>Search name</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Business name…">
                </div>
                <div class="field" style="justify-content:flex-end">
                    <button class="btn primary" type="submit">Filter</button>
                    <a class="btn" href="{{ route('wikidata.saved') }}" style="margin-top:.3rem">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Saved Businesses <span class="wd-badge">{{ $businesses->total() }}</span></h2>
        <a href="{{ route('wikidata.emails') }}" class="btn primary">Get Emails →</a>
    </div>
    <div style="padding:1.25rem">
        @if($businesses->isEmpty())
            <p style="text-align:center;padding:3rem;color:var(--text-muted,#888)">
                No businesses stored yet. <a href="{{ route('wikidata.index') }}">Search and save some first.</a>
            </p>
        @else
        <div class="wd-table-wrap">
            <table class="wd-table">
                <thead>
                    <tr>
                        <th>#</th><th>Name</th><th>City</th><th>Category</th>
                        <th>Website</th><th>Phone</th><th>Address</th><th>Emails</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($businesses as $biz)
                    <tr>
                        <td class="wd-num">{{ $loop->iteration + ($businesses->currentPage()-1)*$businesses->perPage() }}</td>
                        <td><strong>{{ $biz->name }}</strong></td>
                        <td>{{ $biz->city ?? '—' }}</td>
                        <td>{{ $biz->category ?? '—' }}</td>
                        <td>
                            @if($biz->website)
                                <a href="{{ $biz->website }}" target="_blank" rel="noopener">{{ Str::limit($biz->website,40) }}</a>
                            @else <span class="wd-num">—</span> @endif
                        </td>
                        <td>{{ $biz->phone ?? '—' }}</td>
                        <td>{{ $biz->address ?? '—' }}</td>
                        <td><span class="wd-badge" style="{{ $biz->emails_count ? 'background:#16a34a' : 'background:#9ca3af' }}">{{ $biz->emails_count }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($businesses->hasPages())
        <div style="margin-top:1rem">{{ $businesses->links() }}</div>
        @endif
        @endif
    </div>
</div>

@endsection
