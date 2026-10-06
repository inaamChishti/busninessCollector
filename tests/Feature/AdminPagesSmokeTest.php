<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesSmokeTest extends TestCase
{
    use RefreshDatabase;
    public function test_every_primary_admin_page_renders():void
    {
        $user=User::factory()->create(['is_admin'=>true]);$business=Business::create(['business_name'=>'Smoke Test Ltd','country'=>'GB','discovered_at'=>now()]);$this->actingAs($user);
        foreach(['/','/locations','/businesses','/businesses/create','/businesses/'.$business->id,'/businesses/'.$business->id.'/edit','/categories','/emails','/jobs','/failed-jobs','/imports','/exports','/duplicates','/activity','/settings','/profile']as$uri)$this->get($uri)->assertOk();
    }
}
