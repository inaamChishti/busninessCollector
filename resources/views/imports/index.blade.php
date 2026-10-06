@extends('layouts.app')@section('title','Imports')@section('subtitle','Upload postcode and city CSV files')@section('content')
<div class="grid-2"><div class="panel"><h2>Upload location CSV</h2><p>Required headers: <code>postcode,city</code>. Files up to 100 MB are processed in the background.</p><form method="post" action="{{ route('imports.store') }}" enctype="multipart/form-data">@csrf<label>CSV file<input type="file" name="csv" accept=".csv,text/csv,text/plain" required></label><button class="btn primary">Validate and import</button></form></div><div class="panel"><h2>Example</h2><pre>postcode,city
E6 1AA,London
IG1 1AA,Ilford
B1 1AA,Birmingham</pre></div></div>
<div class="table-wrap"><table><thead><tr><th>File</th><th>Status</th><th>Rows</th><th>Imported</th><th>Duplicates</th><th>Failed</th><th>Date</th></tr></thead><tbody>@forelse($imports as $i)<tr><td>{{ $i->filename }}</td><td><x-status :value="$i->status"/></td><td>{{ number_format($i->total_rows) }}</td><td>{{ number_format($i->imported_rows) }}</td><td>{{ number_format($i->duplicate_rows) }}</td><td>{{ number_format($i->failed_rows) }}</td><td>{{ $i->created_at->format('d M Y H:i') }}</td></tr>@empty<tr><td colspan="7" class="empty">No imports yet.</td></tr>@endforelse</tbody></table></div>{{ $imports->links() }}
@endsection
