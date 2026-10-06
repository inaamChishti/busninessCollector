<?php

namespace App\Jobs;

use App\Models\Business;
use App\Models\ExportRecord;
use App\Services\StreamedTabularWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateExportJob implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;
    public int $tries=2;public int $timeout=1800;
    public function __construct(public readonly int $exportId){$this->onQueue('exports');}
    public function handle():void
    {
        $export=ExportRecord::findOrFail($this->exportId);if($export->status==='completed')return;$export->update(['status'=>'processing']);$ext=$export->format==='xlsx'?'xlsx':'csv';$filename='exports/businesses-'.$export->id.'-'.now()->format('Ymd-His').'.'.$ext;Storage::disk('local')->makeDirectory('exports');$path=Storage::disk('local')->path($filename);$writer=new StreamedTabularWriter($path,$ext);
        try{$headers=['business_name','category','address_line_1','address_line_2','city','region','postcode','country','phone','website','primary_email','additional_emails','contact_form_url','email_source_url','provider','provider_business_id','provider_url','latitude','longitude','date_discovered','email_status'];$writer->addRow($headers);$count=0;
            $query=Business::with('emails')->orderBy('id');$filters=$export->filters??[];if(!empty($filters['city']))$query->where('city',$filters['city']);if(!empty($filters['postcode']))$query->where('postcode','like',$filters['postcode'].'%');if(($filters['has_email']??null)==='1')$query->whereNotNull('primary_email');if(($filters['has_email']??null)==='0')$query->whereNull('primary_email');
            $query->chunkById(500,function($businesses)use($writer,&$count){foreach($businesses as$b){$additional=$b->emails->where('is_primary',false)->pluck('email')->implode('; ');$sources=$b->emails->pluck('source_url')->unique()->implode('; ');$writer->addRow([$b->business_name,$b->primary_category,$b->address_line_1,$b->address_line_2,$b->city,$b->region,$b->postcode,$b->country,$b->phone,$b->official_website,$b->primary_email,$additional,$b->contact_form_url,$sources,$b->external_provider,$b->external_business_id,$b->provider_url,$b->latitude,$b->longitude,optional($b->discovered_at)->toIso8601String(),$b->email_status]);$count++;}});
            $writer->close();$export->update(['status'=>'completed','filename'=>$filename,'row_count'=>$count,'completed_at'=>now()]);
        }catch(Throwable$e){try{$writer->close();}catch(Throwable){}$export->update(['status'=>'failed','error'=>mb_substr($e->getMessage(),0,2000)]);throw$e;}
    }
}
