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
				'n1'=>$row[14]??'',
				'c2'=>$row[15]??'',
				'n2'=>$row[16]??'',
				'c3'=>$row[17]??'',
				'n3'=>$row[18]??'',
				'c4'=>$row[19]??'',
				'n4'=>$row[20]??'',
				'c5'=>$row[21]??'',
				'n5'=>$row[22]??'',
				'c6'=>$row[23]??'',
				'n6'=>$row[24]??'',
				'c7'=>$row[25]??'',
				'n7'=>$row[26]??'',
				'c8'=>$row[27]??'',
				'n8'=>$row[28]??'',
				'c9'=>$row[29]??'',
				'n9'=>$row[30]??'',
				'c10'=>$row[31]??'',
				'n10'=>$row[32]??'',
				'c11'=>$row[33]??'',
				'n11'=>$row[34]??'',
				'c12'=>$row[35]??'',
				'n12'=>$row[36]??'',
				'c13'=>$row[37]??'',
				'n13'=>$row[38]??'',
				'c14'=>$row[39]??'',
				'n14'=>$row[40]??'',
				'c15'=>$row[41]??'',
				'n15'=>$row[42]??'',
				'c16'=>$row[43]??'',
				'n16'=>$row[44]??'',
				'c17'=>$row[45]??'',
				'n17'=>$row[46]??'',
				'c18'=>$row[47]??'',
				'n18'=>$row[48]??'',
				'c19'=>$row[49]??'',
				'n19'=>$row[50]??'',
				'c20'=>$row[51]??'',
				'n20'=>$row[52]??'',
				'c21'=>$row[53]??'',
				'n21'=>$row[54]??'',
				'c22'=>$row[55]??'',
				'n22'=>$row[56]??'',
				'c23'=>$row[57]??'',
				'n23'=>$row[58]??'',
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