<?php

namespace App;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\WithMapping;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

//use Maatwebsite\Excel\Concerns\WithMappedCells;

class ExportVaksinTmpData extends DefaultValueBinder implements  FromCollection, WithCustomStartCell,WithHeadings,WithCustomValueBinder,WithColumnWidths,WithStyles
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private $vaksin;
    private $nama_perusahaan;
    //private $kd_perusahaan;


    public function __construct($vaksin,$nama_perusahaan)
    {
      $this->vaksin = $vaksin;
      $this->nama_perusahaan = $nama_perusahaan;
    }

    public function collection()
    {
        return $this->vaksin;
    }
    public function headings(): array
   {
       return [["Nama Perusahaan","",$this->nama_perusahaan],[],[],["No.", "Nama Pegawai", "Status Pegawai (PKWT/PKWTT/Alihdaya)", "Jenis Kelamin (L/P)", "Provinsi",
           "Kota","NIK","Usia","No Handphone","Jumlah Keluarga Inti","Tgl Upload","Status","Keterangan"]];
   }
   public function startCell(): string
    {
        return 'A1';
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 35,
            'C' => 30,
            'D' => 15,
            'G' => 26,
            'H' => 10,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
        ];
    }

  public function styles(Worksheet $sheet)
   {
       return [
           // Style the first row as bold text.
          'A1'    => ['font' => ['bold' => true]],
           4    => ['font' => ['bold' => true]],

       ];
   }



}
