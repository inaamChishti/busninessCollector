@extends('layouts.app')
@section('title','Reports')
@section('subtitle','Postcode-wise business and email collection summary')
@section('content')

{{-- Summary Cards --}}
<div class="metric-grid">
    @php $metrics = [
        ['Total Locations',  $totals['locations'],   'Imported postcodes'],
        ['Total Businesses', $totals['businesses'],  'Discovered records'],
        ['With Email',       $totals['with_email'],  'Emails found'],
        ['With Website',     $totals['with_website'],'Verified websites'],
        ['Contact Form Only',$totals['contact_form'],'No published email'],
        ['Failed',           $totals['failed'],      'Request errors'],
    ]; @endphp
    @foreach($metrics as $m)
    <article class="metric">
        <span>{{ $m[0] }}</span>
        <strong>{{ number_format($m[1]) }}</strong>
        <small>{{ $m[2] }}</small>
    </article>
    @endforeach
</div>

{{-- Filters --}}
<div class="panel">
    <form class="filters" method="get">
        <input name="postcode" value="{{ $filterPostcode }}" placeholder="Postcode prefix (e.g. SW1)">
        <input name="city"     value="{{ $filterCity }}"     placeholder="City">
        <input name="country"  value="{{ $filterCountry }}"  placeholder="Country code (e.g. GB)" maxlength="2">
        <button class="btn">Filter</button>
        <a class="btn" href="{{ route('reports.export', request()->query()) }}">⇩ Export CSV</a>
        @if($filterPostcode || $filterCity || $filterCountry)
            <a class="btn" href="{{ route('reports.index') }}">Clear</a>
        @endif
    </form>
</div>

{{-- Postcode Table --}}
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Postcode</th>
                <th>City</th>
                <th>Country</th>
                <th>Status</th>
                <th>Businesses</th>
                <th>With Website</th>
                <th>✅ With Email</th>
                <th>📋 Contact Form</th>
                <th>❌ No Email</th>
                <th>⚠ Failed</th>
                <th>Email Rate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($locations as $loc)
            @php
                $rate = $loc->total_businesses > 0
                    ? round(($loc->with_email / $loc->total_businesses) * 100)
                    : 0;
                $barColor = $rate >= 60 ? '#22c55e' : ($rate >= 30 ? '#f59e0b' : '#ef4444');
            @endphp
            <tr>
                <td><strong>{{ $loc->postcode }}</strong></td>
                <td>{{ $loc->city ?: '—' }}</td>
                <td>{{ $loc->country }}</td>
                <td><x-status :value="$loc->status"/></td>
                <td>{{ number_format($loc->total_businesses) }}</td>
                <td>{{ number_format($loc->with_website) }}</td>
                <td><strong style="color:#16a34a">{{ number_format($loc->with_email) }}</strong></td>
                <td>{{ number_format($loc->contact_form_only) }}</td>
                <td>{{ number_format($loc->no_email) }}</td>
                <td>{{ number_format($loc->failed) }}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:6px;min-width:80px">
                        <div style="flex:1;background:#e5e7eb;border-radius:4px;height:8px;overflow:hidden">
                            <div style="width:{{ $rate }}%;background:{{ $barColor }};height:100%"></div>
                        </div>
                        <span style="font-size:.8rem;white-space:nowrap">{{ $rate }}%</span>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="11" class="empty">No locations found. Import postcodes first from the Imports page.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $locations->links() }}

{{-- Top Cities --}}
@if($topCities->isNotEmpty())
<div class="panel" style="margin-top:1.5rem">
    <div class="panel-head"><h2>Top Cities by Email Found</h2></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>City</th>
                    <th>Total Businesses</th>
                    <th>With Email</th>
                    <th>Email Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topCities as $i => $city)
                @php $cityRate = $city->total > 0 ? round(($city->with_email / $city->total) * 100) : 0; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $city->city }}</strong></td>
                    <td>{{ number_format($city->total) }}</td>
                    <td><strong style="color:#16a34a">{{ number_format($city->with_email) }}</strong></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;min-width:100px">
                            <div style="flex:1;background:#e5e7eb;border-radius:4px;height:8px;overflow:hidden">
                                <div style="width:{{ $cityRate }}%;background:{{ $cityRate >= 60 ? '#22c55e' : ($cityRate >= 30 ? '#f59e0b' : '#ef4444') }};height:100%"></div>
                            </div>
                            <span style="font-size:.8rem">{{ $cityRate }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
