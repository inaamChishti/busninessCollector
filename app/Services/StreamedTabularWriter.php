<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class StreamedTabularWriter
{
    private mixed $handle; private int $row=0; private ?string $tempDir=null; private ?string $sheetPath=null;
    public function __construct(private readonly string $path,private readonly string $format)
    {
        if($format==='csv'){$this->handle=fopen($path,'wb');if(!$this->handle)throw new RuntimeException('Could not create CSV export.');return;}
        if(!class_exists(ZipArchive::class))throw new RuntimeException('PHP ext-zip is required for XLSX exports.');
        $this->tempDir=dirname($path).'/.xlsx-'.bin2hex(random_bytes(8));if(!mkdir($this->tempDir,0700,true)&&!is_dir($this->tempDir))throw new RuntimeException('Could not create XLSX temporary directory.');$this->sheetPath=$this->tempDir.'/sheet1.xml';$this->handle=fopen($this->sheetPath,'wb');if(!$this->handle)throw new RuntimeException('Could not create XLSX worksheet.');fwrite($this->handle,'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>');
    }
    public function addRow(array$values):void
    {
        $values=array_map(fn($value)=>$this->safeCell($value),$values);if($this->format==='csv'){fputcsv($this->handle,$values);return;}$this->row++;$xml='<row r="'.$this->row.'">';foreach(array_values($values)as$i=>$value){$cell=$this->column($i+1).$this->row;$escaped=htmlspecialchars((string)($value??''),ENT_XML1|ENT_QUOTES,'UTF-8');$xml.='<c r="'.$cell.'" t="inlineStr"><is><t xml:space="preserve">'.$escaped.'</t></is></c>';}$xml.='</row>';fwrite($this->handle,$xml);
    }
    public function close():void
    {
        if(!is_resource($this->handle))return;if($this->format==='csv'){fclose($this->handle);$this->handle=null;return;}fwrite($this->handle,'</sheetData></worksheet>');fclose($this->handle);$this->handle=null;$zip=new ZipArchive();if($zip->open($this->path,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true)throw new RuntimeException('Could not create XLSX archive.');
        $zip->addFromString('[Content_Types].xml','<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
        $zip->addFromString('_rels/.rels','<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml','<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Businesses" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels','<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $zip->addFromString('xl/styles.xml','<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts><fills count="1"><fill><patternFill patternType="none"/></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="1"><xf xfId="0"/></cellXfs></styleSheet>');$zip->addFile($this->sheetPath,'xl/worksheets/sheet1.xml');$zip->close();@unlink($this->sheetPath);@rmdir($this->tempDir);
    }
    private function safeCell(mixed$value):mixed{if(is_string($value)&&preg_match('/^[=+\-@]/',$value))return"'".$value;return$value;}
    private function column(int$n):string{$name='';while($n>0){$n--;$name=chr(65+($n%26)).$name;$n=intdiv($n,26);}return$name;}
}
