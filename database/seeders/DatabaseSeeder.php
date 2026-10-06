<?php

namespace Database\Seeders;

use App\Models\BusinessCategory;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Accountants','accountants'], ['Solicitors','lawyers'], ['Estate agents','realestateagents'],
            ['Restaurants','restaurants'], ['Dentists','dentists'], ['Doctors','physicians'],
            ['Electricians','electricians'], ['Plumbers','plumbing'], ['Builders','contractors'],
            ['Retailers','shopping'], ['Hotels','hotels'], ['Garages','autorepair'],
            ['Beauty salons','beautysvc'], ['Hairdressers','hair'], ['Consultants','businessconsulting'],
            ['IT companies','itservices'], ['Marketing companies','marketing'], ['Education providers','education'],
        ];
        foreach ($categories as $priority => [$name, $alias]) {
            BusinessCategory::query()->firstOrCreate(['name'=>$name], ['api_alias'=>$alias,'search_term'=>$name,'enabled'=>true,'priority'=>($priority + 1) * 10]);
        }

        $settings = [
            'country'=>'GB','collection_paused'=>'true','business_provider'=>'yelp','website_search_provider'=>'google_cse',
            'exact_postcode_match'=>'false','max_api_requests_per_minute'=>'60','max_search_requests_per_minute'=>'30',
            'max_crawl_requests_per_minute'=>'30','request_delay_ms'=>'750','request_timeout'=>'15','max_pages_per_website'=>'12',
            'max_crawl_depth'=>'2','max_concurrent_jobs'=>'2','retry_attempts'=>'3','retry_delay'=>'60','website_confidence_threshold'=>'65',
            'recheck_websites_after_days'=>'90','recheck_no_email_after_days'=>'30','never_recheck_success'=>'true',
            'email_preference_order'=>'info,contact,enquiries,office,admin,sales,hello,support,customerservice',
        ];
        foreach ($settings as $key=>$value) Setting::query()->firstOrCreate(['key'=>$key], ['value'=>$value,'group'=>'general']);
    }
}
