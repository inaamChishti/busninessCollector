<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessEmail;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class BusinessEmailService
{
    public function __construct(private readonly EmailExtractionService $extractor) {}

    public function persistFromDocuments(Business $business, array $documents): int
    {
        $count = 0;
        DB::transaction(function () use ($business,$documents,&$count) {
            foreach ($documents as $document) foreach ($this->extractor->extract($document['html']) as $found) {
                BusinessEmail::updateOrCreate(['business_id'=>$business->id,'email'=>$found['email']], ['email_type'=>$found['type'],'source_url'=>$document['url'],'is_generic'=>$found['is_generic'],'validation_status'=>'domain_valid','first_seen_at'=>now(),'last_seen_at'=>now()]);
            }
            $emails = $business->emails()->get(); $count = $emails->count();
            if ($count) {
                $preferred = array_map('trim', explode(',', (string) Setting::valueOf('email_preference_order','info,contact,enquiries,office,admin,sales,hello,support')));
                $primary = $emails->sortBy(function ($email) use ($preferred) { $local=strstr($email->email,'@',true); $rank=array_search($local,$preferred,true); return $rank===false ? 999 : $rank; })->first();
                $business->emails()->update(['is_primary'=>false]); $primary->update(['is_primary'=>true]);
                $business->update(['primary_email'=>$primary->email,'email_status'=>$count>1?'multiple_emails_found':'email_found','email_checked_at'=>now()]);
            }
        });
        return $count;
    }
}
