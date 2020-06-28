<?php

namespace App;

use App\TmpPerimeter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
//use Maatwebsite\Excel\Concerns\WithMappedCells;

class TmpPerimeterImportSheet1 implements ToCollection,WithCalculatedFormulas 	
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
					'c1'=>trim($row[13])??'',
					'n1'=>trim($row[14])??'',
					'c2'=>trim($row[15])??'',
					'n2'=>trim($row[16])??'',
					'c3'=>trim($row[17])??'',
					'n3'=>trim($row[18])??'',
					'c4'=>trim($row[19])??'',
					'n4'=>trim($row[20])??'',
					'c5'=>trim($row[21])??'',
					'n5'=>trim($row[22])??'',
					'c6'=>trim($row[23])??'',
					'n6'=>trim($row[24])??'',
					'c7'=>trim($row[25])??'',
					'n7'=>trim($row[26])??'',
					'c8'=>trim($row[27])??'',
					'n8'=>trim($row[28])??'',
					'c9'=>trim($row[29])??'',
					'n9'=>trim($row[30])??'',
					'c10'=>trim($row[31])??'',
					'n10'=>trim($row[32])??'',
					'c11'=>trim($row[33])??'',
					'n11'=>trim($row[34])??'',
					'c12'=>trim($row[35])??'',
					'n12'=>trim($row[36])??'',
					'c13'=>trim($row[37])??'',
					'n13'=>trim($row[38])??'',
					'c14'=>trim($row[39])??'',
					'n14'=>trim($row[40])??'',
					'c15'=>trim($row[41])??'',
					'n15'=>trim($row[42])??'',
					'c16'=>trim($row[43])??'',
					'n16'=>trim($row[44])??'',
					'c17'=>trim($row[45])??'',
					'n17'=>trim($row[46])??'',
					'c18'=>trim($row[47])??'',
					'n18'=>trim($row[48])??'',
					'c19'=>trim($row[49])??'',
					'n19'=>trim($row[50])??'',
					'c20'=>trim($row[51])??'',
					'n20'=>trim($row[52])??'',
					'c21'=>trim($row[53])??'',
					'n21'=>trim($row[54])??'',
					'c22'=>trim($row[55])??'',
					'n22'=>trim($row[56])??'',
					'c23'=>trim($row[57])??'',
					'n23'=>trim($row[58])??'',
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