<?php

namespace Tests\Unit;

use App\Services\StreamedTabularWriter;
use Tests\TestCase;
use ZipArchive;

class StreamedTabularWriterTest extends TestCase
{
    public function test_creates_valid_csv_and_xlsx_files():void
    {
        $csv=sys_get_temp_dir().'/collector-'.bin2hex(random_bytes(4)).'.csv';$writer=new StreamedTabularWriter($csv,'csv');$writer->addRow(['name','email']);$writer->addRow(['A & B','info@example.co.uk']);$writer->close();$this->assertStringContainsString('info@example.co.uk',file_get_contents($csv));unlink($csv);
        $xlsx=sys_get_temp_dir().'/collector-'.bin2hex(random_bytes(4)).'.xlsx';$writer=new StreamedTabularWriter($xlsx,'xlsx');$writer->addRow(['name','email']);$writer->addRow(['A & B','info@example.co.uk']);$writer->close();$zip=new ZipArchive();$this->assertTrue($zip->open($xlsx)===true);$this->assertNotFalse($zip->locateName('xl/worksheets/sheet1.xml'));$zip->close();unlink($xlsx);
    }
}
