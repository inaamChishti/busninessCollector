<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filterCity     = $request->input('city');
        $filterPostcode = $request->input('postcode');
        $filterCountry  = $request->input('country');

        // Postcode-wise report from locations table (search postcodes)
        $query = Location::query()
            ->select([
                'locations.postcode',
                'locations.city',
                'locations.country',
                'locations.status',
                'locations.businesses_discovered',
                DB::raw('COUNT(DISTINCT lb.business_id) as total_businesses'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.primary_email IS NOT NULL THEN b.id END) as with_email'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.primary_email IS NULL AND b.email_status = "contact_form_only" THEN b.id END) as contact_form_only'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.primary_email IS NULL AND b.email_status NOT IN ("contact_form_only","pending","queued") THEN b.id END) as no_email'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.email_status IN ("request_failed","crawl_blocked") THEN b.id END) as failed'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.official_website IS NOT NULL THEN b.id END) as with_website'),
            ])
            ->leftJoin('location_business as lb', 'lb.location_id', '=', 'locations.id')
            ->leftJoin('businesses as b', function ($join) {
                $join->on('b.id', '=', 'lb.business_id')
                     ->whereNull('b.deleted_at');
            })
            ->groupBy('locations.id', 'locations.postcode', 'locations.city', 'locations.country', 'locations.status', 'locations.businesses_discovered');

        if ($filterCity)     $query->where('locations.city', 'like', '%'.$filterCity.'%');
        if ($filterPostcode) $query->where('locations.postcode', 'like', $filterPostcode.'%');
        if ($filterCountry)  $query->where('locations.country', $filterCountry);

        $locations = $query->orderBy('locations.postcode')->paginate(50)->withQueryString();

        // Summary totals
        $totals = [
            'locations'       => Location::count(),
            'businesses'      => Business::count(),
            'with_email'      => Business::whereNotNull('primary_email')->count(),
            'with_website'    => Business::whereNotNull('official_website')->count(),
            'failed'          => Business::whereIn('email_status', ['request_failed', 'crawl_blocked'])->count(),
            'contact_form'    => Business::where('email_status', 'contact_form_only')->count(),
        ];

        // Top cities by email count
        $topCities = Business::select('city', DB::raw('COUNT(*) as total'), DB::raw('COUNT(primary_email) as with_email'))
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('with_email')
            ->limit(10)
            ->get();

        return view('reports.index', compact('locations', 'totals', 'topCities', 'filterCity', 'filterPostcode', 'filterCountry'));
    }

    public function export(Request $request)
    {
        $filterCity     = $request->input('city');
        $filterPostcode = $request->input('postcode');
        $filterCountry  = $request->input('country');

        $query = Location::query()
            ->select([
                'locations.postcode',
                'locations.city',
                'locations.country',
                'locations.status',
                DB::raw('COUNT(DISTINCT lb.business_id) as total_businesses'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.primary_email IS NOT NULL THEN b.id END) as with_email'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.primary_email IS NULL AND b.email_status = "contact_form_only" THEN b.id END) as contact_form_only'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.primary_email IS NULL AND b.email_status NOT IN ("contact_form_only","pending","queued") THEN b.id END) as no_email'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.email_status IN ("request_failed","crawl_blocked") THEN b.id END) as failed'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.official_website IS NOT NULL THEN b.id END) as with_website'),
            ])
            ->leftJoin('location_business as lb', 'lb.location_id', '=', 'locations.id')
            ->leftJoin('businesses as b', function ($join) {
                $join->on('b.id', '=', 'lb.business_id')->whereNull('b.deleted_at');
            })
            ->groupBy('locations.id', 'locations.postcode', 'locations.city', 'locations.country', 'locations.status');

        if ($filterCity)     $query->where('locations.city', 'like', '%'.$filterCity.'%');
        if ($filterPostcode) $query->where('locations.postcode', 'like', $filterPostcode.'%');
        if ($filterCountry)  $query->where('locations.country', $filterCountry);

        $rows = $query->orderBy('locations.postcode')->get();

        $filename = 'postcode-report-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Postcode', 'City', 'Country', 'Status', 'Total Businesses', 'With Email', 'Contact Form Only', 'No Email', 'Failed', 'With Website', 'Email Rate %']);
            foreach ($rows as $row) {
                $rate = $row->total_businesses > 0
                    ? round(($row->with_email / $row->total_businesses) * 100, 1)
                    : 0;
                fputcsv($handle, [
                    $row->postcode,
                    $row->city,
                    $row->country,
                    $row->status,
                    $row->total_businesses,
                    $row->with_email,
                    $row->contact_form_only,
                    $row->no_email,
                    $row->failed,
                    $row->with_website,
                    $rate.'%',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
