<?php

namespace App;

use App\TmpPerimeter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
//use Maatwebsite\Excel\Concerns\WithMappedCells;

class TmpPerimeterImportSheet1 implements ToCollection
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private $filename;
    private $kd_perusahaan;

    public function __construct(string $filename,string $kd_perusahaan) 
    {
        $this->filename = $filename;
        $this->kd_perusahaan = $kd_perusahaan;
    }
	
	public function collection(Collection $rows)
    {
		//dd($this->filename);
		
		$i = 0;
        foreach ($rows as $row) 
        {
           if ($i>=4){
			if  ( $row[1] != null ) {
			
				TmpPerimeter::create([
				'region' => $row[1],
				'perimeter'=>$row[2],
				'k_perimeter'=>$row[8]??'',
				'pic'=>$row[9]??'',
				'nik_pic'=>$row[10]??'',
				'level'=>$row[3]??'',
				'fo'=>$row[11]??'',
				'nik_fo'=>$row[12]??'',
				'keterangan'=>$row[4]??'',
				'kd_perusahaan' => $this->kd_perusahaan,
				'status' => 0,
				'c1'=>$row[13]??'',
				'c2'=>$row[14]??'',
				'c3'=>$row[15]??'',
				'c4'=>$row[16]??'',
				'c5'=>$row[17]??'',
				'c6'=>$row[18]??'',
				'c7'=>$row[19]??'',
				'c8'=>$row[20]??'',
				'c9'=>$row[21]??'',
				'c10'=>$row[22]??'',
				'c11'=>$row[23]??'',
				'c12'=>$row[24]??'',
				'c13'=>$row[25]??'',
				'c14'=>$row[26]??'',
				'c15'=>$row[27]??'',
				'c16'=>$row[28]??'',
				'c17'=>$row[29]??'',
				'c18'=>$row[30]??'',
				'c19'=>$row[31]??'',
				'c20'=>$row[32]??'',
				'c21'=>$row[33]??'',
				'c22'=>$row[34]??'',
				'c23'=>$row[35]??'',
				'longitude'=>$row[6]??'',
				'latitude'=>$row[7]??'',
				'alamat'=>$row[5]??'',
				'file'=> $this->filename,
				]);
			//dd('tes');
			}
		   }
		   $i=$i+1;
        }
		
		
    }
}