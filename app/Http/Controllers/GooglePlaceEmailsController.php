<?php

namespace App\Http\Controllers;

use App\Models\GooglePlace;
use App\Models\GooglePlaceEmail;
use Illuminate\Http\Request;

class GooglePlaceEmailsController extends Controller
{
    // ── Index: one row per business, all emails as chips ─────────
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'latest');

        $query = GooglePlace::withCount('emails')
            ->with('emails')
            ->has('emails');

        if ($request->filled('search'))
            $query->whereHas('emails', fn($q) => $q->where('email', 'like', '%'.$request->search.'%'));

        if ($request->filled('city'))
            $query->where(fn($q) => $q->where('locality', 'like', '%'.$request->city.'%')
                ->orWhere('search_location', 'like', '%'.$request->city.'%'));

        if ($request->filled('business'))
            $query->where('name', 'like', '%'.$request->business.'%');

        if ($request->filled('type'))
            $query->where('search_type', $request->type);

        // Apply sort AFTER all filters
        switch ($sort) {
            case 'name':        $query->orderBy('name');                break;
            case 'emails_asc':  $query->orderBy('emails_count', 'asc'); break;
            case 'emails_desc': $query->orderBy('emails_count', 'desc');break;
            case 'city':        $query->orderBy('locality');            break;
            default:            $query->orderBy('created_at', 'desc');  break;
        }

        $places = $query->paginate(50)->withQueryString();

        $cities = GooglePlace::has('emails')
            ->get(['locality','search_location'])
            ->map(fn($p) => $p->locality ?: $p->search_location)
            ->filter()->unique()->sort()->values();

        $types = GooglePlace::has('emails')
            ->distinct()->orderBy('search_type')
            ->pluck('search_type')->filter();

        $stats = [
            'total_emails'    => GooglePlaceEmail::count(),
            'total_businesses'=> GooglePlace::has('emails')->count(),
        ];

        return view('google.emails', compact('places', 'cities', 'types', 'stats', 'sort'));
    }

    // ── Delete single email ───────────────────────────────────────
    public function destroy(GooglePlaceEmail $email)
    {
        $email->delete();
        return response()->json(['status' => 'success']);
    }

    // ── Export to Excel ───────────────────────────────────────────
    public function export(Request $request)
    {
        $query = GooglePlace::with('emails')->has('emails');

        if ($request->filled('search'))
            $query->whereHas('emails', fn($q) => $q->where('email', 'like', '%'.$request->search.'%'));
        if ($request->filled('city'))
            $query->where(fn($q) => $q->where('locality', 'like', '%'.$request->city.'%')
                ->orWhere('search_location', 'like', '%'.$request->city.'%'));
        if ($request->filled('business'))
            $query->where('name', 'like', '%'.$request->business.'%');
        if ($request->filled('type'))
            $query->where('search_type', $request->type);

        $places = $query->get();

        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="google-emails-'.date('Y-m-d').'.xls"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $rows   = [];
        $rows[] = ['#', 'Business Name', 'Email', 'Website', 'Phone', 'Address', 'City', 'Postcode', 'Business Type', 'Rating'];

        $i = 1;
        foreach ($places as $p) {
            foreach ($p->emails as $e) {
                $rows[] = [
                    $i++,
                    $p->name ?? '',
                    $e->email,
                    $p->website ?? '',
                    $p->formatted_phone_number ?? $p->international_phone_number ?? '',
                    $p->formatted_address ?? '',
                    $p->locality ?? $p->search_location ?? '',
                    $p->postal_code ?? '',
                    $p->search_type ?? '',
                    $p->rating ?? '',
                ];
            }
        }

        $output = '';
        foreach ($rows as $row) {
            $output .= implode("\t", array_map(fn($v) => '"'.str_replace('"', '""', $v ?? '').'"', $row))."\n";
        }

        return response($output, 200, $headers);
    }
}
