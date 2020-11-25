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
					'k_perimeter'=>$row[10]??'',
					'pic'=>$row[11]??'',
					'nik_pic'=>$row[12]??'',
					'level'=>$row[3]??'',
					'fo'=>$row[13]??'',
					'nik_fo'=>$row[14]??'',
					'keterangan'=>$row[4]??'',
					'kd_perusahaan' => $this->kd_perusahaan,
					'status' => 0,
					'c1'=>trim($row[15])??'',
					'n1'=>trim($row[16])??'',
					'c2'=>trim($row[17])??'',
					'n2'=>trim($row[18])??'',
					'c3'=>trim($row[19])??'',
					'n3'=>trim($row[20])??'',
					'c4'=>trim($row[21])??'',
					'n4'=>trim($row[22])??'',
					'c5'=>trim($row[23])??'',
					'n5'=>trim($row[24])??'',
					'c6'=>trim($row[25])??'',
					'n6'=>trim($row[26])??'',
					'c7'=>trim($row[27])??'',
					'n7'=>trim($row[28])??'',
					'c8'=>trim($row[29])??'',
					'n8'=>trim($row[30])??'',
					'c9'=>trim($row[31])??'',
					'n9'=>trim($row[32])??'',
					'c10'=>trim($row[33])??'',
					'n10'=>trim($row[34])??'',
					'c11'=>trim($row[35])??'',
					'n11'=>trim($row[36])??'',
					'c12'=>trim($row[37])??'',
					'n12'=>trim($row[38])??'',
					'c13'=>trim($row[39])??'',
					'n13'=>trim($row[40])??'',
					'c14'=>trim($row[41])??'',
					'n14'=>trim($row[42])??'',
					'c15'=>trim($row[43])??'',
					'n15'=>trim($row[44])??'',
					'c16'=>trim($row[45])??'',
					'n16'=>trim($row[46])??'',
					'c17'=>trim($row[47])??'',
					'n17'=>trim($row[48])??'',
					'c18'=>trim($row[49])??'',
					'n18'=>trim($row[50])??'',
					'c19'=>trim($row[51])??'',
					'n19'=>trim($row[52])??'',
					'c20'=>trim($row[53])??'',
					'n20'=>trim($row[54])??'',
					'c21'=>trim($row[55])??'',
					'n21'=>trim($row[56])??'',
					'c22'=>trim($row[57])??'',
					'n22'=>trim($row[58])??'',
					'c23'=>trim($row[59])??'',
					'n23'=>trim($row[60])??'',
					'longitude'=>$row[8]??'',
					'latitude'=>$row[9]??'',
					'alamat'=>$row[5]??'',
					'file'=> $this->filename,
					'provinsi'=>$row[6]??'',
					'kota'=>$row[7]??'',
					]);

					
				//dd('tes');
				}
			   }
			   $i=$i+1;
			}

		
		
    }
	
	public function getColumnCount(): int
    {
        return $this->column;
    }
}