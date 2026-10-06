<?php

namespace App\Jobs;

use App\Models\ImportRecord;
use App\Models\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportLocationsJob implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;
    public int $tries=2;public int $timeout=1200;
    public function __construct(public readonly int $importId,public readonly string $storedPath){$this->onQueue('imports');}
    public function handle():void
    {
        $import=ImportRecord::findOrFail($this->importId);if($import->status==='completed')return;$import->update(['status'=>'processing']);
        $path=Storage::disk('local')->path($this->storedPath);$handle=fopen($path,'rb');if(!$handle)throw new \RuntimeException('Unable to open uploaded CSV.');
        $header=fgetcsv($handle);$normalized=array_map(fn($v)=>strtolower(trim((string)$v)),$header?:[]);$postcodeIndex=array_search('postcode',$normalized,true);$cityIndex=array_search('city',$normalized,true);
        if($postcodeIndex===false||$cityIndex===false){fclose($handle);$import->update(['status'=>'failed','errors'=>['CSV headers must include postcode and city.']]);return;}
        $total=$inserted=$duplicates=$failed=0;$batch=[];
        try{while(($row=fgetcsv($handle))!==false){$total++;$postcode=trim((string)($row[$postcodeIndex]??''));$city=trim((string)($row[$cityIndex]??''));if($postcode===''||$city===''){$failed++;continue;}$normalizedPostcode=Location::normalizePostcode($postcode);$batch[]=['import_id'=>$import->id,'postcode'=>strtoupper($postcode),'postcode_normalized'=>$normalizedPostcode,'city'=>$city,'country'=>'GB','status'=>'pending','created_at'=>now(),'updated_at'=>now()];
                if(count($batch)>=500){[$new,$dupes]=$this->flush($batch);$inserted+=$new;$duplicates+=$dupes;$batch=[];$import->update(['total_rows'=>$total,'imported_rows'=>$inserted,'duplicate_rows'=>$duplicates,'failed_rows'=>$failed]);}}
            if($batch){[$new,$dupes]=$this->flush($batch);$inserted+=$new;$duplicates+=$dupes;}
            $import->update(['status'=>'completed','total_rows'=>$total,'imported_rows'=>$inserted,'duplicate_rows'=>$duplicates,'failed_rows'=>$failed]);
        }catch(Throwable$e){$import->update(['status'=>'failed','errors'=>[$e->getMessage()]]);throw$e;}finally{fclose($handle);}
    }
    private function flush(array $batch):array
    {
        $new=DB::table('locations')->insertOrIgnore($batch);return[$new,count($batch)-$new];
    }
}
