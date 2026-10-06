<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DuplicateController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FailedJobController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\WikidataController;
use App\Http\Controllers\WikidataEmailController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;




Route::get('/setup',[SetupController::class,'create'])->name('setup');
Route::post('/setup',[SetupController::class,'store'])->name('setup.store')->middleware('throttle:5,1');
Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'create'])->name('login');Route::post('/login',[AuthController::class,'store'])->middleware('throttle:5,1');});
Route::post('/logout',[AuthController::class,'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth','admin'])->group(function(){

Route::get('/verify-emails/progress',[VerifyEmailsController::class,'progress'])->name('verify-emails.progress');
    Route::get('/verify-emails/queue',[VerifyEmailsController::class,'queue'])->name('verify-emails.queue');
    Route::post('/verify-emails/verify',[VerifyEmailsController::class,'verify'])->name('verify-emails.verify');


    Route::get('/',DashboardController::class)->name('dashboard');
    Route::post('/collection/start',[CollectionController::class,'start'])->name('collection.start');
    Route::post('/collection/pause',[CollectionController::class,'pause'])->name('collection.pause');
    Route::post('/collection/resume',[CollectionController::class,'resume'])->name('collection.resume');
    Route::post('/collection/retry-failed',[CollectionController::class,'retryFailed'])->name('collection.retry-failed');
    Route::post('/collection/find-websites',[CollectionController::class,'websites'])->name('collection.websites');
    Route::post('/collection/find-emails',[CollectionController::class,'emails'])->name('collection.emails');

     Route::get('/process/progress',[ProcessController::class,'progress'])->name('process.progress');
    Route::get('/locations',[LocationController::class,'index'])->name('locations.index');Route::post('/locations/bulk',[LocationController::class,'bulk'])->name('locations.bulk');Route::delete('/locations/{location}',[LocationController::class,'destroy'])->name('locations.destroy');
    Route::get('/imports',[ImportController::class,'index'])->name('imports.index');Route::post('/imports',[ImportController::class,'store'])->name('imports.store');
    Route::resource('businesses',BusinessController::class);
    Route::post('/businesses/{b}/find-website',[BusinessController::class,'findWebsite'])->name('businesses.find-website');
    Route::post('/businesses/{b}/find-email',[BusinessController::class,'findEmail'])->name('businesses.find-email');
    Route::post('/businesses/{b}/websites/{website}/verify',[BusinessController::class,'verify'])->name('businesses.websites.verify');
    Route::post('/businesses/{b}/websites/{website}/approve',[BusinessController::class,'approveWebsite'])->name('businesses.websites.approve');
    Route::post('/businesses/{b}/websites/{website}/reject',[BusinessController::class,'rejectWebsite'])->name('businesses.websites.reject');
    Route::get('/categories',[CategoryController::class,'index'])->name('categories.index');Route::post('/categories',[CategoryController::class,'store'])->name('categories.store');Route::put('/categories/{category}',[CategoryController::class,'update'])->name('categories.update');Route::delete('/categories/{category}',[CategoryController::class,'destroy'])->name('categories.destroy');Route::post('/categories/toggle-all',[CategoryController::class,'toggleAll'])->name('categories.toggle-all');
    Route::get('/emails',[EmailController::class,'index'])->name('emails.index');Route::post('/emails/{email}/primary',[EmailController::class,'primary'])->name('emails.primary');Route::delete('/emails/{email}',[EmailController::class,'destroy'])->name('emails.destroy');
    Route::get('/jobs',[JobController::class,'index'])->name('jobs.index');Route::get('/failed-jobs',[FailedJobController::class,'index'])->name('failed-jobs.index');Route::post('/failed-jobs/retry',[FailedJobController::class,'retry'])->name('failed-jobs.retry');Route::post('/failed-jobs/retry-all',[FailedJobController::class,'retryAll'])->name('failed-jobs.retry-all');Route::delete('/failed-jobs/{uuid}',[FailedJobController::class,'destroy'])->name('failed-jobs.destroy');
    Route::get('/exports',[ExportController::class,'index'])->name('exports.index');Route::post('/exports',[ExportController::class,'store'])->name('exports.store');Route::get('/exports/{export}/download',[ExportController::class,'download'])->name('exports.download');
    Route::get('/activity',[ActivityController::class,'index'])->name('activity.index');
    Route::get('/duplicates',[DuplicateController::class,'index'])->name('duplicates.index');Route::post('/duplicates/{duplicate}/dismiss',[DuplicateController::class,'dismiss'])->name('duplicates.dismiss');Route::post('/duplicates/{duplicate}/merge',[DuplicateController::class,'merge'])->name('duplicates.merge');
    Route::get('/reports',[ReportController::class,'index'])->name('reports.index');
    Route::get('/reports/export',[ReportController::class,'export'])->name('reports.export');

    Route::get('/settings',[SettingsController::class,'index'])->name('settings.index');Route::put('/settings',[SettingsController::class,'update'])->name('settings.update');Route::post('/settings/api-token',[SettingsController::class,'generateApiToken'])->name('settings.api-token');
    Route::get('/profile',[ProfileController::class,'edit'])->name('profile.edit');Route::put('/profile',[ProfileController::class,'update'])->name('profile.update');
    Route::get('/wikidata',[WikidataController::class,'index'])->name('wikidata.index');
    Route::get('/wikidata/fetch',[WikidataController::class,'fetch'])->name('wikidata.fetch');
    Route::post('/wikidata/store',[WikidataController::class,'store'])->name('wikidata.store');
    Route::get('/wikidata/saved',[WikidataController::class,'saved'])->name('wikidata.saved');
    Route::get('/wikidata/emails',[WikidataEmailController::class,'index'])->name('wikidata.emails');
    Route::get('/wikidata/emails/scrape',[WikidataEmailController::class,'scrape'])->name('wikidata.emails.scrape');
    Route::post('/wikidata/emails/store',[WikidataEmailController::class,'store'])->name('wikidata.emails.store');

     Route::get('/overture',[OvertureController::class,'index'])->name('overture.index');
    Route::post('/overture',[OvertureController::class,'store'])->name('overture.store');
    Route::get('/overture/{overture}',[OvertureController::class,'show'])->name('overture.show');
    Route::delete('/overture/{overture}',[OvertureController::class,'destroy'])->name('overture.destroy');

});


// Route::get('/extract-emails', function () {
//     $urls = [
//         'https://www.kuehne-nagel.com/',
//         'http://starbucks.co.uk',
//         'https://sancarlo.co.uk/restaurants/cheshire/san-carlo-fiorentina-hale/',
//         'https://www.marriottlifestyle.co.uk/'
//     ];

//     $results = [];
//     $excludedExtensions = ['.png', '.jpg', '.jpeg', '.gif', '.svg', '.webp', '.js', '.css', '.woff'];

//     foreach ($urls as $baseUrl) {
//         $emailsFound = [];
//         $visitedUrls = [];
//         $queue = [$baseUrl];
        
//         $maxPagesToCheck = 10; // Har website ke maximum 10 pages check honge taake timeout na ho

//         while (!empty($queue) && count($visitedUrls) < $maxPagesToCheck) {
//             $currentUrl = array_shift($queue);

//             if (in_array($currentUrl, $visitedUrls)) {
//                 continue;
//             }

//             $visitedUrls[] = $currentUrl;

//             try {
//                 $response = Http::timeout(6)
//                     ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64)')
//                     ->get($currentUrl);

//                 if (!$response->successful()) {
//                     continue;
//                 }

//                 $html = $response->body();

//                 // 1. Is page se emails extract karein
//                 $pattern = "/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/";
//                 preg_match_all($pattern, $html, $matches);

//                 if (!empty($matches[0])) {
//                     foreach ($matches[0] as $email) {
//                         $isExcluded = false;
//                         foreach ($excludedExtensions as $ext) {
//                             if (str_ends_with(strtolower($email), $ext)) {
//                                 $isExcluded = true;
//                                 break;
//                             }
//                         }
//                         if (!$isExcluded) {
//                             $emailsFound[] = $email;
//                         }
//                     }
//                 }

//                 // 2. Mazeed sub-pages (links) dhoondhein taake unko bhi check kiya ja sake
//                 libxml_use_internal_errors(true);
//                 $dom = new DOMDocument();
//                 @$dom->loadHTML($html);
//                 $links = $dom->getElementsByTagName('a');

//                 $baseParsed = parse_url($baseUrl);
//                 $baseHost = $baseParsed['host'] ?? '';

//                 foreach ($links as $link) {
//                     $href = $link->getAttribute('href');
//                     $absoluteUrl = resolveLinkUrl($baseUrl, $href);

//                     if ($absoluteUrl) {
//                         $parsedHref = parse_url($absoluteUrl);
//                         // Sirf usi website ke andar ke links allow karein (e.g. contact, about)
//                         if (isset($parsedHref['host']) && $parsedHref['host'] === $baseHost) {
//                             $cleanUrl = strtok($absoluteUrl, '#');
                            
//                             // Agar contact, about ya support page ho toh priority dein, warna queue mein add karein
//                             if (!in_array($cleanUrl, $visitedUrls) && !in_array($cleanUrl, $queue)) {
//                                 if (str_contains(strtolower($cleanUrl), 'contact') || str_contains(strtolower($cleanUrl), 'about') || str_contains(strtolower($cleanUrl), 'support')) {
//                                     array_unshift($queue, $cleanUrl); // Priority queue
//                                 } else {
//                                     $queue[] = $cleanUrl;
//                                 }
//                             }
//                         }
//                     }
//                 }

//             } catch (\Exception $e) {
//                 continue;
//             }
//         }

//         $results[] = [
//             'url' => $baseUrl,
//             'emails' => array_unique($emailsFound)
//         ];
//     }

//     // HTML Table render karna
//     $htmlOutput = '<div style="font-family: Arial, sans-serif; margin: 30px;">';
//     $htmlOutput .= '<h2>Multi-Page Website Email Scraper Results</h2>';
//     $htmlOutput .= '<table border="1" cellpadding="12" cellspacing="0" style="border-collapse: collapse; width: 100%; text-align: left;">';
//     $htmlOutput .= '<tr style="background-color: #f4f4f4;"><th>Website URL</th><th>Found Emails</th></tr>';
    
//     foreach ($results as $res) {
//         $emailList = empty($res['emails']) ? '<span style="color: red;">No direct email found across pages</span>' : implode('<br>', $res['emails']);
//         $htmlOutput .= '<tr>';
//         $htmlOutput .= '<td><a href="'.$res['url'].'" target="_blank">'.$res['url'].'</a></td>';
//         $htmlOutput .= '<td>'.$emailList.'</td>';
//         $htmlOutput .= '</tr>';
//     }
    
//     $htmlOutput .= '</table></div>';

//     return $htmlOutput;
// });

// // Helper function URL resolve karne ke liye
// function resolveLinkUrl($baseUrl, $href) {
//     if (empty($href) || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
//         return null;
//     }
//     if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
//         return $href;
//     }
//     $baseParsed = parse_url($baseUrl);
//     $scheme = $baseParsed['scheme'] ?? 'http';
//     $host = $baseParsed['host'] ?? '';

//     if (str_starts_with($href, '//')) {
//         return $scheme . ':' . $href;
//     }
//     if (str_starts_with($href, '/')) {
//         return $scheme . '://' . $host . $href;
//     }
//     return $scheme . '://' . $host . '/' . ltrim($href, '/');
// }









// use Illuminate\Support\Facades\Http;



// Route::get('/london-mobile-shops', function () {
//     // Wikidata par registered telecommunication / electronics companies & brands
//     $sparqlQuery = '
//     SELECT ?item ?itemLabel ?website WHERE {
//       { ?item wdt:P31 wd:Q15924765 . } UNION   # Telecommunications company
//       { ?item wdt:P31 wd:Q431289 . } UNION     # Brand
//       { ?item wdt:P31 wd:Q1665646 . }          # Mobile phone retailer
      
//       # Headquartered or operating in United Kingdom / London (Q145 = UK, Q84 = London)
//       { ?item wdt:P17 wd:Q145 . } UNION { ?item wdt:P159 wd:Q84 . }
      
//       ?item wdt:P856 ?website .               # Official website mandatory
      
//       SERVICE wikibase:label { bd:serviceParam wikibase:language "en" }
//     }
//     LIMIT 100
//     ';

//     $response = Http::withHeaders([
//         'Accept'     => 'application/json',
//         'User-Agent' => 'LaravelMobileApp/1.0 (inam@example.com)'
//     ])->get('https://query.wikidata.org/sparql', [
//         'query'  => $sparqlQuery,
//         'format' => 'json'
//     ]);

//     if ($response->successful()) {
//         $bindings = $response->json()['results']['bindings'] ?? [];
//         $shops = [];

//         foreach ($bindings as $item) {
//             $shops[] = [
//                 'name'    => $item['itemLabel']['value'] ?? 'Unnamed Business',
//                 'website' => $item['website']['value'] ?? null,
//             ];
//         }

//         // Duplicate websites remove karna
//         $shops = array_unique($shops, SORT_REGULAR);

//         return response()->json([
//             'status' => 'success',
//             'total'  => count($shops),
//             'data'   => array_values($shops)
//         ]);
//     }

//     return response()->json([
//         'error'   => 'Failed to fetch data',
//         'details' => $response->body()
//     ], 500);
// });




// Route::get('/london-restaurants', function () {
//     // Greater London ke andar tamam restaurants, cafes aur pubs jinki website maujood ho
//     $sparqlQuery = '
//     SELECT ?item ?itemLabel ?website WHERE {
//       { ?item wdt:P31 wd:Q11707 . } UNION     # Restaurant
//       { ?item wdt:P31 wd:Q1936384 . } UNION   # Cafe
//       { ?item wdt:P31 wd:Q27611 . }           # Pub
      
//       ?item wdt:P131+ wd:Q23306 .             # Greater London hierarchy
//       ?item wdt:P856 ?website .               # Official website mandatory
      
//       SERVICE wikibase:label { bd:serviceParam wikibase:language "en" }
//     }
//     LIMIT 500
//     ';

//     $response = Http::withHeaders([
//         'Accept'     => 'application/json',
//         'User-Agent' => 'LaravelRestaurantApp/1.0 (inam@example.com)'
//     ])->get('https://query.wikidata.org/sparql', [
//         'query'  => $sparqlQuery,
//         'format' => 'json'
//     ]);

//     if ($response->successful()) {
//         $bindings = $response->json()['results']['bindings'] ?? [];
//         $restaurants = [];

//         foreach ($bindings as $item) {
//             $restaurants[] = [
//                 'name'    => $item['itemLabel']['value'] ?? 'Unnamed Business',
//                 'website' => $item['website']['value'] ?? null,
//             ];
//         }

//         // Duplicate websites remove karna
//         $restaurants = array_unique($restaurants, SORT_REGULAR);

//         return response()->json([
//             'status' => 'success',
//             'total'  => count($restaurants),
//             'data'   => array_values($restaurants)
//         ]);
//     }

//     return response()->json([
//         'error'   => 'Failed to fetch data',
//         'details' => $response->body()
//     ], 500);
// });






// Route::get('/london-restaurants', function () {
//     // wdt:P131+ ki madad se London aur uske tamam boroughs ke restaurants ajayenge
//     $sparqlQuery = '
//     SELECT ?item ?itemLabel ?website WHERE {
//       { ?item wdt:P31 wd:Q11707 . } UNION
//       { ?item wdt:P31 wd:Q1936384 . } UNION
//       { ?item wdt:P31 wd:Q27611 . }
      
//       ?item wdt:P131+ wd:Q23306 .
//       ?item wdt:P856 ?website .
      
//       SERVICE wikibase:label { bd:serviceParam wikibase:language "en" }
//     }
//     LIMIT 200
//     ';

//     $response = Http::withHeaders([
//         'Accept'     => 'application/json',
//         'User-Agent' => 'LaravelRestaurantApp/1.0 (inam@example.com)'
//     ])->get('https://query.wikidata.org/sparql', [
//         'query'  => $sparqlQuery,
//         'format' => 'json'
//     ]);

//     if ($response->successful()) {
//         $bindings = $response->json()['results']['bindings'] ?? [];
//         $restaurants = [];

//         foreach ($bindings as $item) {
//             $restaurants[] = [
//                 'name'    => $item['itemLabel']['value'] ?? 'Unnamed Business',
//                 'website' => $item['website']['value'] ?? null,
//             ];
//         }

//         // Duplicate websites remove karna
//         $restaurants = array_unique($restaurants, SORT_REGULAR);

//         return response()->json([
//             'status' => 'success',
//             'total'  => count($restaurants),
//             'data'   => array_values($restaurants)
//         ]);
//     }

//     return response()->json([
//         'error' => 'Failed to fetch data',
//         'details' => $response->body()
//     ], 500);
// });




// Route::get('/extract-and-filter-emails', function () {
//     $restaurants = [
//         ["name" => "Union Street Café", "website" => "http://www.gordonramsay.com/union-street-cafe/"],
//         ["name" => "Bread Street Kitchen", "website" => "https://www.gordonramsayrestaurants.com/bread-street-kitchen"],
//         ["name" => "Locanda Locatelli", "website" => "http://www.locandalocatelli.com"],
//         ["name" => "Obalende Suya Express Restaurant", "website" => "http://suyaexpress.com/"],
//         ["name" => "Pied à Terre", "website" => "http://www.pied-a-terre.co.uk"],
//         ["name" => "Restaurant Gordon Ramsay", "website" => "https://www.gordonramsayrestaurants.com/restaurant-gordon-ramsay/"],
//         ["name" => "Scandinavian Kitchen", "website" => "https://www.scandikitchen.co.uk/"],
//         ["name" => "Wong Kei", "website" => "http://wongkeilondon.com/"],
//         ["name" => "Bob Bob Ricard", "website" => "http://www.bobbobricard.com/"],
//         ["name" => "Alain Ducasse at The Dorchester", "website" => "https://www.alainducasse-dorchester.com/"],
//         ["name" => "Bar Italia", "website" => "http://baritaliasoho.co.uk/"],
//         ["name" => "Belgo", "website" => "https://www.belgo.com/"],
//         ["name" => "Chez Bruce", "website" => "http://www.chezbruce.co.uk"],
//         ["name" => "Dinner by Heston Blumenthal", "website" => "http://www.mandarinoriental.com/london/fine-dining/dinner-by-heston-blumenthal/"],
//         ["name" => "Galvin at Windows", "website" => "http://www.galvinatwindows.com/"],
//         ["name" => "Hibiscus", "website" => "http://www.hibiscusrestaurant.co.uk/"],
//         ["name" => "The Wolseley", "website" => "https://www.thewolseley.com/"],
//         ["name" => "Veeraswamy", "website" => "http://www.veeraswamy.com"],
//         ["name" => "Yauatcha", "website" => "http://www.yauatcha.com/"],
//         ["name" => "Zafferano", "website" => "https://zafferanorestaurant.com/"],
//         ["name" => "Pollen Street Social", "website" => "http://www.pollenstreetsocial.com/"],
//         ["name" => "China Tang", "website" => "http://www.chinatanglondon.co.uk/"],
//         ["name" => "Gordon Ramsay Plane Food", "website" => "https://www.gordonramsayrestaurants.com/plane-food/"],
//         ["name" => "La Trompette", "website" => "http://www.latrompette.co.uk/"],
//         ["name" => "Lima", "website" => "https://www.limalondon.com/"],
//         ["name" => "Murano", "website" => "http://www.muranolondon.com/"],
//         ["name" => "Scott's", "website" => "http://www.scotts-restaurant.com"],
//         ["name" => "The Bunyadi restaurant", "website" => "http://thebunyadi.com/"],
//         ["name" => "Bocca di Lupo", "website" => "http://www.boccadilupo.com"],
//         ["name" => "Polish Hearth Club", "website" => "http://ogniskopolskie.org.uk"],
//         ["name" => "Bombay Brasserie", "website" => "http://www.bombayb.co.uk/"],
//         ["name" => "Chutney Mary", "website" => "http://www.chutneymary.com/"],
//         ["name" => "Zaika", "website" => "http://www.zaikaofkensington.com/"],
//         ["name" => "Core by Clare Smyth", "website" => "http://www.corebyclaresmyth.com/"],
//         ["name" => "Greenhouse", "website" => "http://www.greenhouserestaurant.co.uk/"],
//         ["name" => "Kitchen Table at Bubbledogs", "website" => "https://kitchentablelondon.co.uk/"],
//         ["name" => "La Dame de Pic", "website" => "http://www.ladamedepiclondon.co.uk/"],
//         ["name" => "River Gardens Cafe", "website" => "https://rivergardenscafe.co.uk/"],
//         ["name" => "ICCO Pizza", "website" => "https://icco.co.uk/"],
//         ["name" => "The Cricketers", "website" => "https://www.harvester.co.uk/restaurants/southeast/thecricketerscroydon"],
//         ["name" => "Park Hotel", "website" => "http://parkhotelteddington.com/"],
//         ["name" => "The River Café", "website" => "https://www.rivercafe.co.uk/"],
//         ["name" => "Simpson's-in-the-Strand", "website" => "http://www.simpsonsinthestrand.co.uk/"],
//         ["name" => "The Dorchester", "website" => "https://www.dorchestercollection.com/en/london/the-dorchester"],
//         ["name" => "Pret a Manger", "website" => "https://www.pret.co.uk/en-GB/shop-finder/l/london/45-47-parsons-green-lane/11159"],
//         ["name" => "Wolves of Tokyo", "website" => "https://www.wolvesoftokyo.com/"],
//         ["name" => "Lillie's", "website" => "https://www.lillieslondon.co.uk/"],
//         ["name" => "Kai Mayfair", "website" => "https://www.kaimayfair.co.uk/"],
//         ["name" => "L'Escargot", "website" => "https://www.lescargot.co.uk/"],
//         ["name" => "Pétrus", "website" => "http://www.gordonramsay.com/petrus/"],
//         ["name" => "Quo Vadis", "website" => "http://www.quovadissoho.co.uk/"],
//         ["name" => "Rules", "website" => "http://www.rules.co.uk/"],
//         ["name" => "Hotel Café Royal", "website" => "https://www.hotelcaferoyal.com/"],
//         ["name" => "Colony", "website" => "http://www.colonybarandgrill.com/"],
//         ["name" => "Criterion Restaurant", "website" => "http://www.criterionrestaurant.com"],
//         ["name" => "Crocker's Folly", "website" => "http://www.crockersfolly.com/"],
//         ["name" => "Chiltern Firehouse", "website" => "http://www.chilternfirehouse.com"],
//         ["name" => "Seashell of Lisson Grove", "website" => "http://www.seashellrestaurant.co.uk/"],
//         ["name" => "The French House", "website" => "http://www.frenchhousesoho.com/"],
//         ["name" => "The Gay Hussar", "website" => "http://gayhussar.co.uk"],
//         ["name" => "67 Pall Mall", "website" => "https://67pallmall.co.uk/"],
//         ["name" => "45 Park Lane", "website" => "https://www.dorchestercollection.com/en/london/45-park-lane"],
//         ["name" => "The Oak W2", "website" => "https://theoaklondon.com"],
//         ["name" => "Le Caprice", "website" => "https://le-caprice.co.uk/"],
//         ["name" => "Sarastro", "website" => "https://www.sarastro-restaurant.com/"],
//         ["name" => "Le Gavroche", "website" => "https://www.le-gavroche.co.uk/"],
//         ["name" => "Langan's Brasserie", "website" => "https://langansbrasserie.com/"],
//         ["name" => "Sketch", "website" => "https://sketch.london/"],
//         ["name" => "The Ledbury", "website" => "http://www.theledbury.com"],
//         ["name" => "Palm House London", "website" => "https://www.palmhouselondon.co.uk/"],
//         ["name" => "Gilgamesh", "website" => "http://www.gilgameshbar.com/"],
//         ["name" => "The Jazz Café", "website" => "https://thejazzcafelondon.com/"],
//         ["name" => "The Churchill Arms", "website" => "https://www.churchillarmskensington.co.uk/"],
//         ["name" => "Pergola Brixton", "website" => "https://www.pergolabrixton.co.uk/"],
//         ["name" => "Westminster Kitchen", "website" => "https://www.westminsterkitchen.com/"],
//         ["name" => "Q138328742", "website" => "https://namastelounge.com/"],
//         ["name" => "Venue 5", "website" => "https://www.venue5.com/"],
//         ["name" => "Casa Tua Camden", "website" => "http://www.casatualondon.com"],
//         ["name" => "Whitehart", "website" => "https://www.dewhitehartrestaurant.com/"],
//         ["name" => "Ace Cafe London", "website" => "http://www.ace-cafe-london.com"],
//         ["name" => "Kitchen Table", "website" => "https://kitchentablelondon.co.uk"],
//         ["name" => "Bourne and Hollingsworth Buildings", "website" => "https://www.bandhbuildings.com/"],
//         ["name" => "The M Kitchen", "website" => "https://themkitchen.co.uk/"],
//         ["name" => "Pergola On The Wharf", "website" => "https://www.pergolacanarywharf.co.uk/"],
//         ["name" => "London Silo", "website" => "silolondon.com"],
//         ["name" => "The Partridge", "website" => "https://www.partridgebromley.co.uk/"],
//         ["name" => "The Amersham Arms", "website" => "https://www.theamershamarms.net"],
//         ["name" => "Sahara Lounge", "website" => "https://www.saharastanmore.co.uk/"],
//         ["name" => "Spice Rack", "website" => "https://www.spiceracklounge.com/"],
//         ["name" => "Clayton Hotel Chiswick", "website" => "https://www.claytonhotelchiswick.com/"],
//         ["name" => "Peckham Bazaar", "website" => "https://www.peckhambazaar.com/"],
//         ["name" => "Harvester", "website" => "https://www.harvester.co.uk/restaurants/london/theunicornromford"],
//         ["name" => "Mercato Metropolitano in Elephant and Castle", "website" => "https://mercatometropolitano.com/locations/elephant-and-castle/"],
//         ["name" => "Tamarind", "website" => "http://www.tamarindrestaurant.com/"],
//         ["name" => "Comptoir Libanais, South Kensington", "website" => "https://www.comptoirlibanais.com/locations/south-kensington/"]
//     ];

//     set_time_limit(300);
//     $results = [];

//     foreach ($restaurants as $item) {
//         $name = $item['name'];
//         $url = $item['website'];
//         $validEmails = [];

//         try {
//             $response = Http::withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64)')
//                             ->timeout(10)
//                             ->get($url);

//             if ($response->successful()) {
//                 $html = $response->body();
//                 preg_match_all("/[a-zA-Z0-9_.+\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+/", $html, $matches);

//                 if (!empty($matches[0])) {
//                     $uniqueEmails = array_values(array_unique($matches[0]));

//                     foreach ($uniqueEmails as $email) {
//                         $email = strtolower(trim($email));

//                         // 1. Junk & Code Pattern Exclusion (Sentry, JS libraries, image assets, placeholders)
//                         $isJunk = false;
//                         $junkKeywords = ['sentry.io', 'wixpress.com', 'domain.com', 'example.com', 'react', 'bootstrap', 'lodash', 'sentry-next'];
                        
//                         foreach ($junkKeywords as $keyword) {
//                             if (str_contains($email, $keyword)) {
//                                 $isJunk = true;
//                                 break;
//                             }
//                         }

//                         // Check file extensions (e.g. image@2x.png)
//                         $ext = pathinfo($email, PATHINFO_EXTENSION);
//                         if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'js', 'css', 'json'])) {
//                             $isJunk = true;
//                         }

//                         // Agar junk nahi hai toh valid list mein daal dein
//                         if (!$isJunk) {
//                             $validEmails[] = $email;
//                         }
//                     }

//                     // 2. Campaign Targeting Sort (Prioritize general business/sales/info emails over reservations)
//                     usort($validEmails, function($a, $b) {
//                         $priorityKeywords = ['info', 'hello', 'contact', 'enquiries', 'enquiry', 'office', 'manager'];
                        
//                         $scoreA = 0;
//                         $scoreB = 0;

//                         foreach ($priorityKeywords as $keyword) {
//                             if (str_starts_with($a, $keyword)) $scoreA += 10;
//                             if (str_starts_with($b, $keyword)) $scoreB += 10;
//                         }

//                         return $scoreB <=> $scoreA; // High score wali email pehle aayegi
//                     });

//                     $validEmails = array_values(array_unique($validEmails));
//                 }
//             }
//         } catch (\Exception $e) {
//             $validEmails = [];
//         }

//         // Sirf wohi record rakhein jin ki valid campaign emails mil gayeen hain
//         if (!empty($validEmails)) {
//             $results[] = [
//                 'name' => $name,
//                 'website' => $url,
//                 'best_campaign_email' => $validEmails[0], // Top priority email for direct campaign
//                 'all_valid_emails' => $validEmails       // Backup emails agar zaroorat paray
//             ];
//         }
//     }

//     return response()->json([
//         'status' => 'success',
//         'total_matched' => count($results),
//         'data' => $results
//     ]);
// });