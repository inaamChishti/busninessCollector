<?php

namespace App\Http\Controllers;

use App\Models\WikidataBusiness;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WikidataController extends Controller
{
    // Cities with their Wikidata QIDs
    public static array $cities = [
        'London'     => 'Q84',
        'Manchester' => 'Q18125',
        'Birmingham' => 'Q2256',
        'Leeds'      => 'Q48958',
        'Glasgow'    => 'Q4093',
        'Liverpool'  => 'Q24826',
        'Edinburgh'  => 'Q23436',
        'Bristol'    => 'Q23154',
        'Sheffield'  => 'Q42448',
        'Newcastle'  => 'Q1425428',
        'Nottingham' => 'Q41367',
        'Cardiff'    => 'Q3422',
       
    ];

    // Business categories with their Wikidata type QIDs
    public static array $categories = [
        'Restaurants'    => [['Q11707', 'Restaurant'], ['Q1936384', 'Cafe'], ['Q27611', 'Pub']],
        'Mobile Shops'   => [['Q15924765', 'Telecom company'], ['Q431289', 'Brand'], ['Q1665646', 'Mobile phone retailer']],
        'Hotels'         => [['Q27686', 'Hotel'], ['Q2025869', 'Boutique hotel']],
        'Hospitals'      => [['Q16917', 'Hospital'], ['Q1469824', 'Medical clinic']],
        'Banks'          => [['Q22687', 'Bank']],
        'Supermarkets'   => [['Q180846', 'Supermarket'], ['Q1378827', 'Grocery store']],
        'Gyms & Fitness' => [['Q988108', 'Sports club'], ['Q7832979', 'Fitness centre']],
        'Schools'        => [['Q3914', 'School'], ['Q23002054', 'Secondary school']],
        'Law Firms'      => [['Q613142', 'Law firm']],
        'Real Estate'    => [['Q1371849', 'Real estate agency']],
    ];

    // ── Search page ────────────────────────────────────────────────
    public function index()
    {
        return view('wikidata.index', [
            'cities'     => array_keys(self::$cities),
            'categories' => array_keys(self::$categories),
        ]);
    }

    // ── SPARQL fetch (AJAX) ─────────────────────────────────────────
    public function fetch(Request $request)
    {
        $cityName     = $request->input('city');
        $categoryName = $request->input('category');

        if (!isset(self::$cities[$cityName])) {
            return response()->json(['error' => 'Invalid city selected.'], 422);
        }
        if (!isset(self::$categories[$categoryName])) {
            return response()->json(['error' => 'Invalid category selected.'], 422);
        }

        $cityQid  = self::$cities[$cityName];
        $typeQids = self::$categories[$categoryName];

        $typeUnions = collect($typeQids)
            ->map(fn($t) => "{ ?item wdt:P31 wd:{$t[0]} . }")
            ->implode(" UNION\n  ");

        $sparqlQuery = "
SELECT ?item ?itemLabel ?website ?phone ?address WHERE {
  {$typeUnions}

  { ?item wdt:P17 wd:Q145 . ?item wdt:P131+ wd:{$cityQid} . }
  UNION
  { ?item wdt:P159 wd:{$cityQid} . }
  UNION
  { ?item wdt:P131+ wd:{$cityQid} . }

  ?item wdt:P856 ?website .

  OPTIONAL { ?item wdt:P1329 ?phone . }
  OPTIONAL { ?item wdt:P969  ?address . }

  SERVICE wikibase:label { bd:serviceParam wikibase:language \"en\" }
}
LIMIT 300
";

        $response = Http::withHeaders([
            'Accept'     => 'application/json',
            'User-Agent' => 'BusinessCollectorApp/1.0 (contact@example.com)',
        ])->timeout(30)->get('https://query.wikidata.org/sparql', [
            'query'  => $sparqlQuery,
            'format' => 'json',
        ]);

        if (!$response->successful()) {
            return response()->json([
                'error'   => 'Wikidata API request failed.',
                'details' => $response->body(),
            ], 500);
        }

        $bindings = $response->json()['results']['bindings'] ?? [];
        $results  = [];

        foreach ($bindings as $item) {
            $results[] = [
                'name'    => $item['itemLabel']['value'] ?? 'Unnamed',
                'website' => $item['website']['value']   ?? null,
                'phone'   => $item['phone']['value']     ?? null,
                'address' => $item['address']['value']   ?? null,
            ];
        }

        $results = array_values(array_unique($results, SORT_REGULAR));

        return response()->json([
            'status'   => 'success',
            'city'     => $cityName,
            'category' => $categoryName,
            'total'    => count($results),
            'data'     => $results,
        ]);
    }

    // ── Store fetched results to DB ─────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'city'     => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'data'     => 'required|array|min:1',
            'data.*.name' => 'required|string|max:255',
        ]);

        $city     = $request->input('city');
        $category = $request->input('category');
        $inserted = 0;
        $skipped  = 0;

        foreach ($request->input('data') as $row) {
            $name    = trim($row['name']    ?? '');
            $website = trim($row['website'] ?? '');

            if (!$name) { $skipped++; continue; }

            $exists = WikidataBusiness::where('name', $name)
                ->where('city', $city)
                ->where('website', $website ?: null)
                ->exists();

            if ($exists) { $skipped++; continue; }

            WikidataBusiness::create([
                'name'     => $name,
                'website'  => $website ?: null,
                'phone'    => trim($row['phone']   ?? '') ?: null,
                'address'  => trim($row['address'] ?? '') ?: null,
                'city'     => $city,
                'category' => $category,
            ]);

            $inserted++;
        }

        return response()->json([
            'status'   => 'success',
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'message'  => "{$inserted} saved, {$skipped} skipped (already exist).",
        ]);
    }

    // ── Saved businesses list (for email page) ──────────────────────
    public function saved(Request $request)
    {
        $query = WikidataBusiness::withCount('emails')->latest();

        if ($request->filled('city'))     $query->where('city', $request->city);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('search'))   $query->where('name', 'like', '%'.$request->search.'%');

        $businesses = $query->paginate(50)->withQueryString();

        $cities     = WikidataBusiness::distinct()->orderBy('city')->pluck('city');
        $categories = WikidataBusiness::distinct()->orderBy('category')->pluck('category');

        return view('wikidata.saved', compact('businesses', 'cities', 'categories'));
    }
}
