<?php

namespace App;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Carbon\Carbon;

//use Maatwebsite\Excel\Concerns\WithMappedCells;

class ExportCosmicIndex implements FromCollection, WithCustomStartCell,WithHeadings
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private $cosmicindex;
    //private $kd_perusahaan;


    public function __construct($cosmicindex)
    {
      $this->cosmicindex = $cosmicindex;
    }

    public function collection()
    {
        return $this->cosmicindex;
    }
    public function headings(): array
   {
       return ["Week", "Week_Name", "Kode_Perusahaan", "Nama_Perusahaan","Kode_Sektor", "Nama_Sektor",
     "Cosmic_Index","Pemenuhan_Protokol","Pemenuhan_Monitoring","Pemenuhan_Evidence","Jumlah_Perimeter"];
   }
   public function startCell(): string
    {
        return 'A2';
    }

}
