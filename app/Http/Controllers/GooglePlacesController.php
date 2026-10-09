<?php

namespace App\Http\Controllers;

use App\Models\GooglePlace;
use App\Models\GooglePlaceEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GooglePlacesController extends Controller
{
    private string $apiKey;
    private string $newBaseUrl = 'https://places.googleapis.com/v1/places';

    // Google Places supported types for dropdown
    public static array $placeTypes = [
        'restaurant'             => 'Restaurants',
        'cafe'                   => 'Cafes',
        'bar'                    => 'Bars & Pubs',
        'bakery'                 => 'Bakeries',
        'meal_takeaway'          => 'Takeaways',
        'meal_delivery'          => 'Food Delivery',
        'night_club'             => 'Night Clubs',
        'food'                   => 'Food (General)',
        'hospital'               => 'Hospitals',
        'doctor'                 => 'Doctors / GP',
        'dentist'                => 'Dentists',
        'pharmacy'               => 'Pharmacies',
        'physiotherapist'        => 'Physiotherapists',
        'veterinary_care'        => 'Vets',
        'health'                 => 'Health (General)',
        'store'                  => 'Stores (General)',
        'supermarket'            => 'Supermarkets',
        'grocery_or_supermarket' => 'Grocery Stores',
        'clothing_store'         => 'Clothing Stores',
        'shoe_store'             => 'Shoe Stores',
        'electronics_store'      => 'Electronics Stores',
        'furniture_store'        => 'Furniture Stores',
        'hardware_store'         => 'Hardware Stores',
        'home_goods_store'       => 'Home Goods Stores',
        'jewelry_store'          => 'Jewellery Stores',
        'book_store'             => 'Book Stores',
        'pet_store'              => 'Pet Stores',
        'bicycle_store'          => 'Bicycle Stores',
        'florist'                => 'Florists',
        'liquor_store'           => 'Liquor Stores',
        'convenience_store'      => 'Convenience Stores',
        'department_store'       => 'Department Stores',
        'shopping_mall'          => 'Shopping Malls',
        'lawyer'                 => 'Lawyers / Solicitors',
        'accounting'             => 'Accountants',
        'insurance_agency'       => 'Insurance Agencies',
        'real_estate_agency'     => 'Estate Agents',
        'travel_agency'          => 'Travel Agencies',
        'employment_agency'      => 'Recruitment Agencies',
        'beauty_salon'           => 'Beauty Salons',
        'hair_care'              => 'Hair Salons',
        'spa'                    => 'Spas',
        'gym'                    => 'Gyms & Fitness',
        'car_dealer'             => 'Car Dealers',
        'car_rental'             => 'Car Rentals',
        'car_repair'             => 'Car Repair & Garages',
        'car_wash'               => 'Car Wash',
        'gas_station'            => 'Petrol Stations',
        'bank'                   => 'Banks',
        'atm'                    => 'ATMs',
        'lodging'                => 'Hotels & Lodging',
        'school'                 => 'Schools',
        'secondary_school'       => 'Secondary Schools',
        'university'             => 'Universities',
        'library'                => 'Libraries',
        'airport'                => 'Airports',
        'bus_station'            => 'Bus Stations',
        'subway_station'         => 'Subway Stations',
        'train_station'          => 'Train Stations',
        'taxi_stand'             => 'Taxi Stands',
        'parking'                => 'Parking',
        'church'                 => 'Churches',
        'mosque'                 => 'Mosques',
        'synagogue'              => 'Synagogues',
        'hindu_temple'           => 'Hindu Temples',
        'place_of_worship'       => 'Places of Worship',
        'museum'                 => 'Museums',
        'art_gallery'            => 'Art Galleries',
        'movie_theater'          => 'Cinemas',
        'stadium'                => 'Stadiums',
        'amusement_park'         => 'Amusement Parks',
        'zoo'                    => 'Zoos',
        'aquarium'               => 'Aquariums',
        'bowling_alley'          => 'Bowling Alleys',
        'casino'                 => 'Casinos',
        'city_hall'              => 'City Halls',
        'courthouse'             => 'Courthouses',
        'embassy'                => 'Embassies',
        'fire_station'           => 'Fire Stations',
        'police'                 => 'Police Stations',
        'post_office'            => 'Post Offices',
        'laundry'                => 'Laundry',
        'locksmith'              => 'Locksmiths',
        'moving_company'         => 'Moving Companies',
        'painter'                => 'Painters',
        'plumber'                => 'Plumbers',
        'electrician'            => 'Electricians',
        'roofing_contractor'     => 'Roofers',
        'storage'                => 'Storage Facilities',
        'funeral_home'           => 'Funeral Homes',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.google_places.key', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    // ── Search page ──────────────────────────────────────────────
    public function index()
    {
        return view('google.search', [
            'placeTypes'    => self::$placeTypes,
            'apiConfigured' => $this->isConfigured(),
        ]);
    }

    // ── Text Search AJAX (Places API New) ────────────────────────
    public function search(Request $request)
    {
        $request->validate([
            'query'    => 'nullable|string|max:200',
            'type'     => 'nullable|string|max:100',
            'location' => 'nullable|string|max:200',
            'radius'   => 'nullable|integer|min:100|max:50000',
        ]);

        if (!$this->isConfigured()) {
            return response()->json(['error' => 'Google Places API key not configured. Add GOOGLE_PLACES_API_KEY to .env.'], 503);
        }

        $query    = trim($request->input('query', ''));
        $type     = $request->input('type') ?? '';
        $location = $request->input('location') ?? '';
        $radius   = (int) $request->input('radius', 5000);

        if (!$query && !$location && !$type) {
            return response()->json(['error' => 'Please provide a query, location, or type.'], 422);
        }

        $effectiveQuery = $query ?: ($type ? str_replace('_', ' ', $type) : 'businesses');
        $textQuery      = $effectiveQuery . ($location ? " in {$location}" : '');

        $fieldMask = implode(',', [
            'places.id',
            'places.displayName',
            'places.formattedAddress',
            'places.addressComponents',
            'places.location',
            'places.internationalPhoneNumber',
            'places.nationalPhoneNumber',
            'places.websiteUri',
            'places.googleMapsUri',
            'places.types',
            'places.primaryType',
            'places.businessStatus',
            'places.priceLevel',
            'places.rating',
            'places.userRatingCount',
            'places.regularOpeningHours',
            'places.currentOpeningHours',
            'places.editorialSummary',
            'places.delivery',
            'places.dineIn',
            'places.takeout',
            'places.reservable',
            'places.servesBreakfast',
            'places.servesLunch',
            'places.servesDinner',
            'places.servesBeer',
            'places.servesWine',
            'places.servesBrunch',
            'places.servesVegetarianFood',
            'places.photos',
            'places.plusCode',
            'places.utcOffsetMinutes',
            'places.iconMaskBaseUri',
            'places.iconBackgroundColor',
            'places.accessibilityOptions',
            'places.shortFormattedAddress',
            'nextPageToken',
        ]);

        $allResults = [];
        $nextToken  = null;
        $pages      = 0;
        $maxPages   = 3;

        do {
            $body = ['textQuery' => $textQuery, 'languageCode' => 'en'];
            if ($type)      $body['includedType'] = $type;
            if ($nextToken) $body['pageToken']    = $nextToken;

            $response = Http::withHeaders([
                'Content-Type'     => 'application/json',
                'X-Goog-Api-Key'   => $this->apiKey,
                'X-Goog-FieldMask' => $fieldMask,
            ])->timeout(20)->post("{$this->newBaseUrl}:searchText", $body);

            if (!$response->successful()) {
                $err = $response->json()['error']['message'] ?? ('HTTP ' . $response->status());
                return response()->json(['error' => "Google API error: {$err}"], 500);
            }

            $data       = $response->json();
            $results    = $data['places'] ?? [];
            $allResults = array_merge($allResults, $results);
            $nextToken  = $data['nextPageToken'] ?? null;
            $pages++;

        } while ($nextToken && $pages < $maxPages);

        $mapped = array_map(
            fn($r) => $this->mapResultNew($r, $effectiveQuery, $location, $type),
            $allResults
        );

        return response()->json([
            'status'  => 'success',
            'total'   => count($mapped),
            'query'   => $effectiveQuery,
            'location'=> $location,
            'type'    => $type ? (self::$placeTypes[$type] ?? $type) : 'All Types',
            'data'    => $mapped,
        ]);
    }

    // ── Get place details (Places API New) ───────────────────────
    public function details(Request $request)
    {
        $request->validate(['place_id' => 'required|string']);

        if (!$this->isConfigured()) {
            return response()->json(['error' => 'API key not configured.'], 503);
        }

        $fieldMask = implode(',', [
            'id','displayName','formattedAddress','addressComponents',
            'internationalPhoneNumber','nationalPhoneNumber',
            'websiteUri','googleMapsUri','shortFormattedAddress',
            'location','plusCode','types','primaryType',
            'businessStatus','priceLevel','rating','userRatingCount',
            'regularOpeningHours','currentOpeningHours','secondaryOpeningHours',
            'editorialSummary','iconMaskBaseUri','iconBackgroundColor',
            'photos','reviews','utcOffsetMinutes',
            'accessibilityOptions',
            'delivery','dineIn','takeout','reservable',
            'servesBreakfast','servesLunch','servesDinner',
            'servesBeer','servesWine','servesBrunch','servesVegetarianFood',
        ]);

        // Places API New uses place resource name format
        $placeId  = $request->input('place_id');
        $name     = str_starts_with($placeId, 'places/') ? $placeId : "places/{$placeId}";

        $response = Http::withHeaders([
            'X-Goog-Api-Key'   => $this->apiKey,
            'X-Goog-FieldMask' => $fieldMask,
        ])->timeout(15)->get("{$this->newBaseUrl}/{$name}");

        if (!$response->successful()) {
            $err = $response->json()['error']['message'] ?? ('HTTP ' . $response->status());
            return response()->json(['error' => "Details error: {$err}"], 500);
        }

        return response()->json(['status' => 'success', 'data' => $response->json()]);
    }

    // ── Store to DB ───────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'places'            => 'required|array|min:1',
            'places.*.place_id' => 'required|string',
            'places.*.name'     => 'required|string',
        ]);

        $inserted = 0;
        $skipped  = 0;
        $updated  = 0;

        foreach ($request->input('places') as $place) {
            $placeId = $place['place_id'] ?? '';
            if (!$placeId) { $skipped++; continue; }

            $data     = $this->prepareForStorage($place);
            $existing = GooglePlace::where('place_id', $placeId)->first();

            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                GooglePlace::create($data);
                $inserted++;
            }
        }

        return response()->json([
            'status'   => 'success',
            'inserted' => $inserted,
            'updated'  => $updated,
            'skipped'  => $skipped,
            'message'  => "{$inserted} new, {$updated} updated, {$skipped} skipped.",
        ]);
    }

    // ── Saved results listing ─────────────────────────────────────
    public function saved(Request $request)
    {
        $sort  = $request->input('sort', 'latest');
        $query = GooglePlace::withCount('emails')->with('emails');

        if ($request->filled('search'))      $query->where('name', 'like', '%'.$request->search.'%');
        if ($request->filled('type'))        $query->where('search_type', $request->type);
        if ($request->filled('location'))    $query->where(function($q) use ($request) {
            $q->where('locality', 'like', '%'.$request->location.'%')
              ->orWhere('search_location', 'like', '%'.$request->location.'%');
        });
        if ($request->filled('status'))      $query->where('business_status', $request->status);
        if ($request->filled('postal_code')) $query->where('postal_code', 'like', '%'.$request->postal_code.'%');

        switch ($sort) {
            case 'name':     $query->orderBy('name');               break;
            case 'rating':   $query->orderByDesc('rating');         break;
            case 'city':     $query->orderBy('locality');           break;
            case 'emails':   $query->orderByDesc('emails_count');   break;
            default:         $query->orderByDesc('created_at');     break;
        }

        $places = $query->paginate(50)->withQueryString();
        $types  = GooglePlace::distinct()->orderBy('search_type')->pluck('search_type')->filter();
        $stats  = [
            'total'        => GooglePlace::count(),
            'with_phone'   => GooglePlace::whereNotNull('formatted_phone_number')->count(),
            'with_website' => GooglePlace::whereNotNull('website')->count(),
            'operational'  => GooglePlace::where('business_status', 'OPERATIONAL')->count(),
            'with_email'   => GooglePlaceEmail::distinct('google_place_id')->count('google_place_id'),
        ];

        return view('google.saved', compact('places', 'types', 'stats', 'sort'));
    }

    // ── Delete a saved place ──────────────────────────────────────
    public function destroyPlace(GooglePlace $place)
    {
        $place->emails()->delete();
        $place->delete();
        return response()->json(['status' => 'success']);
    }

    // ── Scrape emails from a website ─────────────────────────────
    public function scrapeEmails(Request $request)
    {
        $request->validate([
            'website'  => 'required|url|max:500',
            'place_id' => 'nullable|integer|exists:google_places,id',
        ]);

        $baseUrl    = rtrim($request->input('website'), '/');
        $emails     = [];
        $visited    = [];
        $queue      = [$baseUrl];
        $maxPages   = 8;

        while (!empty($queue) && count($visited) < $maxPages) {
            $url = array_shift($queue);
            if (!$this->sameDomain($baseUrl, $url)) continue;
            if (in_array($url, $visited))           continue;
            $visited[] = $url;

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; BusinessCollector/1.0)',
                    'Accept'     => 'text/html,application/xhtml+xml',
                ])->timeout(10)->get($url);
            } catch (\Throwable) { continue; }

            if (!$response->successful()) continue;
            $body = $response->body();

            // Extract emails
            preg_match_all('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $body, $matches);
            foreach ($matches[0] as $email) {
                $email = strtolower(trim($email));

                $skip = false;

                // ── 1. File extension endings ─────────────────────
                $skipExtensions = [
                    '.png','.jpg','.jpeg','.gif','.webp','.svg','.ico','.bmp','.tiff',
                    '.woff','.woff2','.ttf','.otf','.eot',
                    '.css','.js','.map','.json','.xml','.pdf',
                    '.mp4','.mp3','.avi','.mov','.zip','.tar','.gz','.rar',
                    '.html','.htm','.php','.aspx',
                ];
                foreach ($skipExtensions as $ext) {
                    if (str_ends_with($email, $ext)) { $skip = true; break; }
                }

                // ── 2. Domain part contains dimension like 300x200, 768x926 ──
                // e.g. paying-by-card@300x200.jpeg  or  image@300x.webp
                if (preg_match('/@.*([\d]+x[\d]*|[\d]*x[\d]+)/i', $email)) $skip = true;

                // ── 3. Local part patterns (before @) ─────────────
                $localPart = explode('@', $email)[0] ?? '';
                // image3, img2x, photo300, thumb_300x200, paying-by-card-in-restaurant-300x200
                if (preg_match('/\d+x\d*/i', $localPart))                   $skip = true;
                if (preg_match('/[\-_]?\d{2,4}x\d{2,4}/i', $localPart))    $skip = true;
                if (preg_match('/^(image|img|photo|thumb|icon|logo|banner|bg|background|sprite|asset|media|file|upload|static)\d*/i', $localPart)) $skip = true;
                if (preg_match('/@\d+x/i', $email))                         $skip = true;
                // paying-by-card-in-restaurant (long slug with hyphens = likely filename)
                if (substr_count($localPart, '-') >= 4)                     $skip = true;

                // ── 4. Known junk domains ──────────────────────────
                $junkDomains = ['sentry.io','example.com','yourdomain.com','domain.com','test.com','placeholder.com','noreply.com','wordpress.org','gravatar.com','wixpress.com','squarespace.com','shopify.com'];
                foreach ($junkDomains as $junk) {
                    if (str_contains($email, $junk)) { $skip = true; break; }
                }

                // ── 5. TLD is numeric (fake: .300, .768, .200) ────
                if (preg_match('/\.\d{2,4}$/', $email)) $skip = true;

                // ── 6. Must have real TLD (2-10 alpha chars) ──────
                if (!preg_match('/\.[a-z]{2,10}$/', $email)) $skip = true;

                // ── 7. Must have @, local >= 2 chars, domain >= 4 ─
                $parts = explode('@', $email);
                if (count($parts) !== 2 || strlen($parts[0]) < 2 || strlen($parts[1]) < 4) $skip = true;

                if ($skip) continue;
                $emails[$email] = $url;
            }

            // Queue internal links
            if (count($visited) < $maxPages) {
                preg_match_all('/<a[^>]+href=["\']([^"\'#?]+)["\'][^>]*>/i', $body, $links);
                foreach ($links[1] as $href) {
                    $href = $this->absoluteUrl($baseUrl, $href);
                    if (!$href || !$this->sameDomain($baseUrl, $href) || in_array($href, $visited)) continue;
                    $lower = strtolower($href);
                    if (str_contains($lower, 'contact') || str_contains($lower, 'about') ||
                        str_contains($lower, 'reach')   || str_contains($lower, 'support')) {
                        array_unshift($queue, $href);
                    } else {
                        $queue[] = $href;
                    }
                }
                $queue = array_values(array_unique($queue));
            }
        }

        $result = collect($emails)->map(fn($src, $email) => [
            'email'      => $email,
            'source_url' => $src,
        ])->values()->all();

        return response()->json([
            'status'        => 'success',
            'website'       => $baseUrl,
            'pages_crawled' => count($visited),
            'total'         => count($result),
            'data'          => $result,
        ]);
    }

    // ── Store scraped emails ──────────────────────────────────────
    public function storeEmails(Request $request)
    {
        $request->validate([
            'place_id'        => 'required|integer|exists:google_places,id',
            'emails'          => 'required|array|min:1',
            'emails.*.email'  => 'required|email|max:255',
        ]);

        $placeId  = $request->input('place_id');
        $inserted = 0;
        $skipped  = 0;

        foreach ($request->input('emails') as $row) {
            $email     = strtolower(trim($row['email']));
            $sourceUrl = trim($row['source_url'] ?? '') ?: null;

            $exists = GooglePlaceEmail::where('google_place_id', $placeId)
                ->where('email', $email)->exists();

            if ($exists) { $skipped++; continue; }

            GooglePlaceEmail::create([
                'google_place_id' => $placeId,
                'email'           => $email,
                'source_url'      => $sourceUrl,
            ]);
            $inserted++;
        }

        $stored = GooglePlaceEmail::where('google_place_id', $placeId)
            ->orderBy('email')->get(['id', 'email', 'source_url']);

        return response()->json([
            'status'   => 'success',
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'message'  => "{$inserted} saved, {$skipped} already existed.",
            'stored'   => $stored,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────
    private function sameDomain(string $base, string $url): bool
    {
        $b = parse_url($base); $u = parse_url($url);
        return isset($u['host'], $b['host']) &&
               strtolower($u['host']) === strtolower($b['host']);
    }

    private function absoluteUrl(string $base, string $href): ?string
    {
        $href = trim($href);
        if (!$href || str_starts_with($href, 'mailto:') ||
            str_starts_with($href, 'tel:')    ||
            str_starts_with($href, 'javascript:')) return null;
        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) return $href;
        $p = parse_url($base);
        $scheme = $p['scheme'] ?? 'https';
        $host   = $p['host']   ?? '';
        if (str_starts_with($href, '//')) return $scheme . ':' . $href;
        if (str_starts_with($href, '/'))  return $scheme . '://' . $host . $href;
        $path = isset($p['path']) ? dirname($p['path']) : '';
        return $scheme . '://' . $host . '/' . ltrim($path . '/' . $href, '/');
    }
    private function mapResultNew(array $r, string $query, string $location = '', string $type = ''): array
    {
        $loc  = $r['location'] ?? [];
        $plus = $r['plusCode']  ?? [];
        $name = $r['displayName']['text'] ?? ($r['displayName'] ?? null);

        // Extract address components
        $components = $r['addressComponents'] ?? [];
        $get = fn($t) => collect($components)
            ->first(fn($c) => in_array($t, $c['types'] ?? []))['longText'] ?? null;

        return [
            'place_id'                   => $r['id']                             ?? null,
            'name'                       => $name,
            'search_query'               => $query,
            'search_location'            => $location,
            'search_type'                => $type,
            'formatted_address'          => $r['formattedAddress']               ?? null,
            'vicinity'                   => $r['shortFormattedAddress']          ?? null,
            'formatted_phone_number'     => $r['nationalPhoneNumber']            ?? null,
            'international_phone_number' => $r['internationalPhoneNumber']       ?? null,
            'website'                    => $r['websiteUri']                     ?? null,
            'url'                        => $r['googleMapsUri']                  ?? null,
            'lat'                        => $loc['latitude']                     ?? null,
            'lng'                        => $loc['longitude']                    ?? null,
            'types'                      => $r['types']                          ?? [],
            'primary_type'               => $r['primaryType']                    ?? null,
            'business_status'            => $r['businessStatus']                 ?? null,
            'price_level'                => isset($r['priceLevel']) ? str_replace('PRICE_LEVEL_', '', $r['priceLevel']) : null,
            'rating'                     => $r['rating']                         ?? null,
            'user_ratings_total'         => $r['userRatingCount']                ?? null,
            'open_now'                   => $r['regularOpeningHours']['openNow'] ?? ($r['currentOpeningHours']['openNow'] ?? null),
            'opening_hours'              => $r['regularOpeningHours']            ?? null,
            'current_opening_hours'      => $r['currentOpeningHours']            ?? null,
            'plus_code'                  => $plus['globalCode']                  ?? null,
            'compound_code'              => $plus['compoundCode']                ?? null,
            'icon'                       => $r['iconMaskBaseUri']                ?? null,
            'icon_background_color'      => $r['iconBackgroundColor']            ?? null,
            'photos'                     => array_slice($r['photos'] ?? [], 0, 5),
            'editorial_summary'          => $r['editorialSummary']['text']       ?? null,
            'delivery'                   => $r['delivery']                       ?? null,
            'dine_in'                    => $r['dineIn']                         ?? null,
            'takeout'                    => $r['takeout']                        ?? null,
            'reservable'                 => $r['reservable']                     ?? null,
            'serves_breakfast'           => $r['servesBreakfast']                ?? null,
            'serves_lunch'               => $r['servesLunch']                    ?? null,
            'serves_dinner'              => $r['servesDinner']                   ?? null,
            'serves_beer'                => $r['servesBeer']                     ?? null,
            'serves_wine'                => $r['servesWine']                     ?? null,
            'serves_brunch'              => $r['servesBrunch']                   ?? null,
            'serves_vegetarian_food'     => $r['servesVegetarianFood']           ?? null,
            'utc_offset_minutes'         => $r['utcOffsetMinutes']               ?? null,
            'address_components'         => $components,
            'street_number'              => $get('street_number'),
            'route'                      => $get('route'),
            'sublocality'                => $get('sublocality_level_1') ?? $get('sublocality'),
            'locality'                   => $get('locality') ?? $get('postal_town'),
            'postal_town'                => $get('postal_town'),
            'postal_code'                => $get('postal_code'),
            'administrative_area_level_1'=> $get('administrative_area_level_1'),
            'administrative_area_level_2'=> $get('administrative_area_level_2'),
            'country'                    => $get('country'),
            'wheelchair_accessible_entrance' => $r['accessibilityOptions']['wheelchairAccessibleEntrance'] ?? null,
        ];
    }

    // ── Prepare for DB storage ────────────────────────────────────
    private function prepareForStorage(array $p): array
    {
        return [
            'place_id'            => $p['place_id'],
            'name'                => $p['name'],
            'search_query'        => $p['search_query']         ?? null,
            'search_location'     => $p['search_location']      ?? null,
            'search_type'         => $p['search_type']          ?? null,
            'formatted_phone_number'     => $p['formatted_phone_number']     ?? null,
            'international_phone_number' => $p['international_phone_number'] ?? null,
            'website'             => $p['website']              ?? null,
            'formatted_address'   => $p['formatted_address']    ?? null,
            'street_number'       => $p['street_number']        ?? null,
            'route'               => $p['route']                ?? null,
            'sublocality'         => $p['sublocality']          ?? null,
            'locality'            => $p['locality']             ?? null,
            'administrative_area_level_1' => $p['administrative_area_level_1'] ?? null,
            'administrative_area_level_2' => $p['administrative_area_level_2'] ?? null,
            'country'             => $p['country']              ?? null,
            'postal_code'         => $p['postal_code']          ?? null,
            'postal_town'         => $p['postal_town']          ?? null,
            'lat'                 => $p['lat']                  ?? null,
            'lng'                 => $p['lng']                  ?? null,
            'plus_code'           => $p['plus_code']            ?? null,
            'compound_code'       => $p['compound_code']        ?? null,
            'types'               => $p['types']                ?? null,
            'primary_type'        => $p['primary_type']         ?? null,
            'business_status'     => $p['business_status']      ?? null,
            'price_level'         => $p['price_level']          ?? null,
            'rating'              => $p['rating']               ?? null,
            'user_ratings_total'  => $p['user_ratings_total']   ?? null,
            'permanently_closed'  => $p['permanently_closed']   ?? false,
            'open_now'            => $p['open_now']             ?? null,
            'opening_hours'       => $p['opening_hours']        ?? null,
            'current_opening_hours'   => $p['current_opening_hours']   ?? null,
            'secondary_opening_hours' => $p['secondary_opening_hours'] ?? null,
            'editorial_summary'   => $p['editorial_summary']    ?? null,
            'icon'                => $p['icon']                 ?? null,
            'icon_background_color'  => $p['icon_background_color']  ?? null,
            'icon_mask_base_uri'  => $p['icon_mask_base_uri']   ?? null,
            'photos'              => $p['photos']               ?? null,
            'url'                 => $p['url']                  ?? null,
            'vicinity'            => $p['vicinity']             ?? null,
            'wheelchair_accessible_entrance' => $p['wheelchair_accessible_entrance'] ?? null,
            'delivery'            => $p['delivery']             ?? null,
            'dine_in'             => $p['dine_in']              ?? null,
            'takeout'             => $p['takeout']              ?? null,
            'reservable'          => $p['reservable']           ?? null,
            'serves_breakfast'    => $p['serves_breakfast']     ?? null,
            'serves_lunch'        => $p['serves_lunch']         ?? null,
            'serves_dinner'       => $p['serves_dinner']        ?? null,
            'serves_beer'         => $p['serves_beer']          ?? null,
            'serves_wine'         => $p['serves_wine']          ?? null,
            'serves_brunch'       => $p['serves_brunch']        ?? null,
            'serves_vegetarian_food' => $p['serves_vegetarian_food'] ?? null,
            'reviews'             => $p['reviews']              ?? null,
            'utc_offset_minutes'  => $p['utc_offset_minutes']   ?? null,
            'address_components'  => $p['address_components']   ?? null,
            'fetched_at'          => now(),
        ];
    }
}
