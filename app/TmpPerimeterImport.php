<?php

namespace App;



use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TmpPerimeterImport implements WithMultipleSheets 
{
    private $filename;
    private $kd_perusahaan;

    public function __construct(string $filename,string $kd_perusahaan) 
    {
        $this->filename = $filename;
        $this->kd_perusahaan = $kd_perusahaan;
    }
    
    public function sheets(): array
    {
        return [
            new TmpPerimeterImportSheet1($this->filename,$this->kd_perusahaan)
        ];
    }
}