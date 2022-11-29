<?php
mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server



$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='ketua' or jabatan='wakil' or jabatan='hakim'");
while($kontak=mysql_fetch_array($cek_kontak)){
	$no_telpon_hakim[$kontak['idsipp']]=$kontak['nomorhp'];	
	$jumlah_perkarahakim[$kontak['idsipp']]=0;
	$perkara_km[$kontak['idsipp']]='';
	$nama_km[$kontak['idsipp']]=$kontak['nama'];
}


//print_r($no_telpon_hakim);

?>



		<?php 
		
		$cek_pendaftaran=mysql_query("SELECT a.perkara_id,
a.tanggal_pendaftaran,
a.pihak1_text,
a.pihak2_text,
a.nomor_perkara,
a.jenis_perkara_text,
a.proses_terakhir_text,
DATEDIFF(CURRENT_DATE, a.tanggal_pendaftaran) AS lamaproses,
b.penetapan_majelis_hakim,
b.majelis_hakim_nama,
b.majelis_hakim_id,
b.majelis_hakim_text,
b.penetapan_panitera_pengganti,
b.panitera_pengganti_text,
b.penetapan_jurusita,
b.jurusita_text,
c.tanggal_putusan
FROM sipp.perkara a
LEFT JOIN sipp.perkara_penetapan b ON a.perkara_id = b.perkara_id
LEFT JOIN sipp.perkara_putusan c ON a.perkara_id = c.perkara_id 	
WHERE a.tanggal_pendaftaran IS NOT NULL
AND a.tahapan_terakhir_id > 10
AND b.penetapan_majelis_hakim IS NOT NULL
AND b.penetapan_panitera_pengganti IS NOT NULL
AND b.penetapan_jurusita IS NOT NULL
AND b.penetapan_hari_sidang IS NULL 
AND c.tanggal_putusan IS NULL");


while($r=mysql_fetch_array($cek_pendaftaran)){
//$breaks = array("<br />","<br>","<br/>","</br>");  
	//			$majelis = str_ireplace($breaks, "\r\n", $r['majelis_hakim_text']);

//$tanggal1  = tgl_indo($r['tanggal_pendaftaran']);
$km=explode(",",$r['majelis_hakim_id']);
$jumlah_perkarahakim[$km[0]]=$jumlah_perkarahakim[$km[0]]+1;
$perkara_km[$km[0]]=$perkara_km[$km[0]].$r['nomor_perkara'].'<br/>';

  }


foreach($jumlah_perkarahakim as $key => $jum_perkara ){

	if($jum_perkara>0){
		$breaks = array("<br />","<br>","<br/>","</br>");  
				$text = str_ireplace($breaks, "\r\n", $perkara_km[$key]);
			//	echo $noperkara;
  $pesan="Info SIPP - Perkara Baru yang belum dilakukan Penetapan Hari Sidang oleh Ketua Majelis ".$nama_km[$key]." = $jum_perkara Perkara \r\nBerikut Nomor Perkara yang Belum PHS \r\n$text";
  //echo $pesan;
		$a=$no_telpon_hakim[$key];
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim) values ('$a','$pesan','t',null,null)");	
		
		
	}
}
		
			?>