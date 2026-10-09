<?php

namespace App\Http\Controllers;

use App\Models\CompanyLead;
use App\Services\CompaniesHouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CompanySearchController extends Controller
{
    public function __construct(private CompaniesHouseService $ch) {}

    // ── Search page ──────────────────────────────────────────────────
    public function index()
    {
        $postcodeAreas = $this->getPostcodeAreas();

        // Build grouped categories for optgroup dropdown
        $rawCategories = config('business_categories');
        $grouped = [];
        foreach ($rawCategories as $key => $cat) {
            $grouped[$cat['group']][] = ['key' => $key, 'label' => $cat['label']];
        }
        ksort($grouped);

        $statuses = ['active', 'dissolved', 'liquidation', 'administration', 'any'];

        return view('companies.search', [
            'postcodeAreas' => $postcodeAreas,
            'grouped'       => $grouped,
            'statuses'      => $statuses,
            'apiConfigured' => $this->ch->isConfigured(),
        ]);
    }

    // ── Postcode area map (shared) ────────────────────────────────────
    private function getPostcodeAreas(): array
    {
        return [
            'AB' => 'AB – Aberdeen',       'AL' => 'AL – St Albans',
            'B'  => 'B – Birmingham',      'BA' => 'BA – Bath',
            'BB' => 'BB – Blackburn',      'BD' => 'BD – Bradford',
            'BH' => 'BH – Bournemouth',    'BL' => 'BL – Bolton',
            'BN' => 'BN – Brighton',       'BR' => 'BR – Bromley',
            'BS' => 'BS – Bristol',        'BT' => 'BT – Belfast',
            'CA' => 'CA – Carlisle',       'CB' => 'CB – Cambridge',
            'CF' => 'CF – Cardiff',        'CH' => 'CH – Chester',
            'CM' => 'CM – Chelmsford',     'CO' => 'CO – Colchester',
            'CR' => 'CR – Croydon',        'CT' => 'CT – Canterbury',
            'CV' => 'CV – Coventry',       'CW' => 'CW – Crewe',
            'DA' => 'DA – Dartford',       'DD' => 'DD – Dundee',
            'DE' => 'DE – Derby',          'DG' => 'DG – Dumfries',
            'DH' => 'DH – Durham',         'DL' => 'DL – Darlington',
            'DN' => 'DN – Doncaster',      'DT' => 'DT – Dorchester',
            'DY' => 'DY – Dudley',
            'E'  => 'E – East London',     'EC' => 'EC – East Central London',
            'EH' => 'EH – Edinburgh',      'EN' => 'EN – Enfield',
            'EX' => 'EX – Exeter',
            'FK' => 'FK – Falkirk',        'FY' => 'FY – Blackpool',
            'G'  => 'G – Glasgow',         'GL' => 'GL – Gloucester',
            'GU' => 'GU – Guildford',      'GY' => 'GY – Guernsey',
            'HA' => 'HA – Harrow',         'HD' => 'HD – Huddersfield',
            'HG' => 'HG – Harrogate',      'HP' => 'HP – Hemel Hempstead',
            'HR' => 'HR – Hereford',       'HS' => 'HS – Hebrides',
            'HU' => 'HU – Hull',           'HX' => 'HX – Halifax',
            'IG' => 'IG – Ilford',         'IM' => 'IM – Isle of Man',
            'IP' => 'IP – Ipswich',        'IV' => 'IV – Inverness',
            'JE' => 'JE – Jersey',
            'KA' => 'KA – Kilmarnock',     'KT' => 'KT – Kingston upon Thames',
            'KW' => 'KW – Wick',           'KY' => 'KY – Kirkcaldy',
            'L'  => 'L – Liverpool',       'LA' => 'LA – Lancaster',
            'LD' => 'LD – Llandrindod Wells', 'LE' => 'LE – Leicester',
            'LL' => 'LL – Llandudno',      'LN' => 'LN – Lincoln',
            'LS' => 'LS – Leeds',          'LU' => 'LU – Luton',
            'M'  => 'M – Manchester',      'ME' => 'ME – Medway',
            'MK' => 'MK – Milton Keynes',  'ML' => 'ML – Motherwell',
            'N'  => 'N – North London',    'NE' => 'NE – Newcastle upon Tyne',
            'NG' => 'NG – Nottingham',     'NN' => 'NN – Northampton',
            'NP' => 'NP – Newport',        'NR' => 'NR – Norwich',
            'NW' => 'NW – North West London',
            'OL' => 'OL – Oldham',         'OX' => 'OX – Oxford',
            'PA' => 'PA – Paisley',        'PE' => 'PE – Peterborough',
            'PH' => 'PH – Perth',          'PL' => 'PL – Plymouth',
            'PO' => 'PO – Portsmouth',     'PR' => 'PR – Preston',
            'RG' => 'RG – Reading',        'RH' => 'RH – Redhill',
            'RM' => 'RM – Romford',
            'S'  => 'S – Sheffield',       'SA' => 'SA – Swansea',
            'SE' => 'SE – South East London', 'SG' => 'SG – Stevenage',
            'SK' => 'SK – Stockport',      'SL' => 'SL – Slough',
            'SM' => 'SM – Sutton',         'SN' => 'SN – Swindon',
            'SO' => 'SO – Southampton',    'SP' => 'SP – Salisbury',
            'SR' => 'SR – Sunderland',     'SS' => 'SS – Southend-on-Sea',
            'ST' => 'ST – Stoke-on-Trent', 'SW' => 'SW – South West London',
            'SY' => 'SY – Shrewsbury',
            'TA' => 'TA – Taunton',        'TD' => 'TD – Galashiels',
            'TF' => 'TF – Telford',        'TN' => 'TN – Tonbridge',
            'TQ' => 'TQ – Torquay',        'TR' => 'TR – Truro',
            'TS' => 'TS – Teesside',       'TW' => 'TW – Twickenham',
            'UB' => 'UB – Southall',
            'W'  => 'W – West London',     'WA' => 'WA – Warrington',
            'WC' => 'WC – West Central London', 'WD' => 'WD – Watford',
            'WF' => 'WF – Wakefield',      'WN' => 'WN – Wigan',
            'WR' => 'WR – Worcester',      'WS' => 'WS – Walsall',
            'WV' => 'WV – Wolverhampton',
            'YO' => 'YO – York',           'ZE' => 'ZE – Lerwick (Shetland)',
        ];
    }

    // ── Live AJAX search ─────────────────────────────────────────────
    public function search(Request $request)
    {
        $request->validate([
            'location'       => 'required|string|max:100',
            'category'       => 'nullable|string',
            'status'         => 'nullable|string|in:active,dissolved,liquidation,administration,any',
            'size'           => 'nullable|integer|min:1|max:5000',
            'exact_postcode' => 'nullable|boolean',
        ]);

        if (!$this->ch->isConfigured()) {
            return response()->json([
                'error' => 'Companies House API key is not configured. Add COMPANIES_HOUSE_API_KEY to your .env file.',
            ], 503);
        }

        $categoryKey = $request->input('category');
        $categoryCfg = $categoryKey ? config('business_categories.' . $categoryKey) : null;

        if ($categoryKey && !$categoryCfg) {
            return response()->json(['error' => 'Invalid category selected.'], 422);
        }

        $location = trim($request->input('location'));  // area code e.g. "BA"
        $status   = $request->input('status', 'active');
        $size     = (int) $request->input('size', 100);
        $exact    = (bool) $request->input('exact_postcode', false);
        $sicCodes = $categoryCfg ? $categoryCfg['sic_codes'] : [];
        $categoryLabel = $categoryCfg ? $categoryCfg['label'] : 'All Categories';

        // Resolve city name from area code to pass to the API
        // Companies House `location` expects a city/town name, not a postcode area code
        $allAreas   = $this->getPostcodeAreas();
        $areaLabel  = $allAreas[strtoupper($location)] ?? '';
        $apiLocation = $location; // fallback: send the code itself
        if (str_contains($areaLabel, '–')) {
            $cityPart = trim(explode('–', $areaLabel)[1]); // e.g. "Bath", "St Albans", "Bolton"
            // For London sub-areas use just "London" so the API returns London companies
            $apiLocation = str_contains(strtolower($cityPart), 'london') ? 'London' : $cityPart;
        }

        try {
            // Fetch more from API than requested so postcode-area filter still yields enough results.
            // Companies House mixes postcodes in results, so we over-fetch then trim.
            $fetchSize = min($size * 20, 5000);

            // Only add sic_codes if a category is selected
            if (!empty($sicCodes)) {
                $data = $this->ch->searchByLocationAndSic(
                    $apiLocation,
                    $sicCodes,
                    $status === 'any' ? '' : $status,
                    $fetchSize
                );
            } else {
                // No category — search by location only
                $response = \Illuminate\Support\Facades\Http::withBasicAuth(
                    config('services.companies_house.key'), ''
                )->timeout(30)->get(
                    config('services.companies_house.base_url') . '/advanced-search/companies',
                    array_filter([
                        'location'       => $apiLocation,
                        'company_status' => $status === 'any' ? null : $status,
                        'size'           => $fetchSize,
                    ])
                );

                if ($response->status() === 404) {
                    $data = ['hits' => 0, 'items' => []];
                } elseif (!$response->successful()) {
                    throw new \RuntimeException('Companies House API returned ' . $response->status());
                } else {
                    $data = $response->json();
                }
            }

            $items = $data['items'] ?? [];

            // Optional exact postcode filter
            if ($exact) {
                $items = CompaniesHouseService::filterExactPostcode($items, $location);
            }

            // Always filter by postcode area prefix (e.g. "B" only matches B1, B2…B99, NOT BD, BN, BS)
            // Filter: only keep companies whose postcode starts with the selected area prefix.
            // e.g. "BA" keeps BA1, BA2, BA3 — removes B1, B2, BB1 etc.
            $areaPrefix = strtoupper(trim($location));

            $items = array_values(array_filter($items, function ($company) use ($areaPrefix) {
                $postcode = strtoupper(trim($company['registered_office_address']['postal_code'] ?? ''));
                // Extract only the leading letters (stop at first digit)
                preg_match('/^([A-Z]+)/', $postcode, $m);
                return isset($m[1]) && $m[1] === $areaPrefix;
            }));

            // Trim to user-requested size after filtering
            if (count($items) > $size) {
                $items = array_slice($items, 0, $size);
            }

            // Map to clean format
            $results = array_map(
                fn($item) => CompaniesHouseService::mapItem($item, $categoryLabel, $location),
                $items
            );

            return response()->json([
                'status'        => 'success',
                'location'      => $location,
                'category'      => $categoryLabel,
                'total_api'     => $data['hits'] ?? count($items),
                'total'         => count($results),
                'exact_filter'  => $exact,
                'data'          => $results,
                'message'       => count($results) === 0
                    ? 'No companies found for this postcode and category. Try a broader location (e.g. city name instead of full postcode).'
                    : null,
            ]);

        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Store selected results to DB ─────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'data'                    => 'required|array|min:1',
            'data.*.company_number'   => 'required|string|max:20',
            'data.*.company_name'     => 'required|string|max:255',
        ]);

        $inserted = 0;
        $skipped  = 0;

        foreach ($request->input('data') as $row) {
            $num = trim($row['company_number'] ?? '');
            if (!$num) { $skipped++; continue; }

            $exists = CompanyLead::where('company_number', $num)->exists();
            if ($exists) { $skipped++; continue; }

            CompanyLead::create([
                'company_number'   => $num,
                'company_name'     => trim($row['company_name']   ?? ''),
                'company_status'   => $row['company_status']       ?? null,
                'company_type'     => $row['company_type']         ?? null,
                'address_line_1'   => $row['address_line_1']       ?? null,
                'address_line_2'   => $row['address_line_2']       ?? null,
                'locality'         => $row['locality']             ?? null,
                'postal_code'      => $row['postal_code']          ?? null,
                'country'          => $row['country']              ?? null,
                'sic_codes'        => $row['sic_codes']            ?? null,
                'date_of_creation' => $row['date_of_creation']     ?? null,
                'category'         => $row['category']             ?? null,
                'search_location'  => $row['search_location']      ?? null,
                'source'           => 'companies_house',
                'fetched_at'       => now(),
            ]);

            $inserted++;
        }

        return response()->json([
            'status'   => 'success',
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'message'  => "{$inserted} saved, {$skipped} skipped (already in database).",
        ]);
    }

    // ── Saved leads listing ──────────────────────────────────────────
    public function saved(Request $request)
    {
        $query = CompanyLead::query()->latest();

        if ($request->filled('category'))     $query->where('category', 'like', '%'.$request->category.'%');
        if ($request->filled('status'))       $query->where('company_status', $request->status);
        if ($request->filled('postcode'))     $query->where('postal_code', 'like', strtoupper($request->postcode).'%');
        if ($request->filled('search'))       $query->where('company_name', 'like', '%'.$request->search.'%');

        $leads      = $query->paginate(50)->withQueryString();
        $categories = CompanyLead::distinct()->orderBy('category')->pluck('category')->filter();
        $stats      = [
            'total'    => CompanyLead::count(),
            'active'   => CompanyLead::where('company_status', 'active')->count(),
            'with_email' => CompanyLead::whereNotNull('email')->count(),
        ];

        return view('companies.saved', compact('leads', 'categories', 'stats'));
    }

    // ── Auto-migrate endpoint ────────────────────────────────────────
    public function migrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            return response()->json([
                'status'  => 'success',
                'message' => 'Migrations ran successfully.',
                'output'  => Artisan::output(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
