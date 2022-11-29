<?php
function formatcurrency($floatcurr, $curr = "USD"){
	$currencies['IDR'] = array(2,',','.');			//	Indonesia, Rupiah
	$currencies['USD'] = array(2,'.',',');			//	US Dollar
	
	return number_format($floatcurr,$currencies[$curr][0],$currencies[$curr][1],$currencies[$curr][2]);
}

	function tgl_indo($tgl){
			$tanggal = substr($tgl,8,2);
			$bulan = getBulan(substr($tgl,5,2));
			$tahun = substr($tgl,0,4);
			return $tanggal.' '.$bulan.' '.$tahun;		 
	}	
	function tgl_indoshort($tgl){
			$tanggal = substr($tgl,8,2);
			$bulan = sortBulan(substr($tgl,5,2));
			$tahun = substr($tgl,0,4);
			return $tanggal.'-'.$bulan.'-'.$tahun;		 
	}
	function tgljam_indo($tgl){
			$tanggal = substr($tgl,8,2);
			$bulan = sortBulan(substr($tgl,5,2));
			$tahun = substr($tgl,0,4);
			$jam = substr($tgl,11,2);
			$menit = substr($tgl,14,2);
			$detik = substr($tgl,17,2);
			return $tanggal.'-'.$bulan.'-'.$tahun.' '.$jam.':'.$menit.':'.$detik;		 
	}

	function getBulan($bln){
				switch ($bln){
					case 1: 
						return "Januari";
						break;
					case 2:
						return "Februari";
						break;
					case 3:
						return "Maret";
						break;
					case 4:
						return "April";
						break;
					case 5:
						return "Mei";
						break;
					case 6:
						return "Juni";
						break;
					case 7:
						return "Juli";
						break;
					case 8:
						return "Agustus";
						break;
					case 9:
						return "September";
						break;
					case 10:
						return "Oktober";
						break;
					case 11:
						return "Nopember";
						break;
					case 12:
						return "Desember";
						break;
				}
			} 
	function sortBulan($bln){
				switch ($bln){
					case 1: 
						return "Jan";
						break;
					case 2:
						return "Feb";
						break;
					case 3:
						return "Mar";
						break;
					case 4:
						return "Apr";
						break;
					case 5:
						return "Mei";
						break;
					case 6:
						return "Jun";
						break;
					case 7:
						return "Jul";
						break;
					case 8:
						return "Agu";
						break;
					case 9:
						return "Sep";
						break;
					case 10:
						return "Okt";
						break;
					case 11:
						return "Nop";
						break;
					case 12:
						return "Des";
						break;
				}
			} 
?>
