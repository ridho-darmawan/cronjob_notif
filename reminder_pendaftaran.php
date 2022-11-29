
<?php
	mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server


//include 'function_app.php';	

$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='ketua' or jabatan='wakil'");
while($kontak=mysql_fetch_array($cek_kontak)){
	$no_telpon_pimpinan[]=$kontak['nomorhp'];	
}




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
c.tanggal_putusan
FROM sipp.perkara a
LEFT JOIN sipp.perkara_penetapan b ON a.perkara_id = b.perkara_id
LEFT JOIN sipp.perkara_putusan c ON a.perkara_id = c.perkara_id 	
WHERE a.tanggal_pendaftaran IS NOT NULL
AND a.tahapan_terakhir_id = 10
AND b.penetapan_majelis_hakim IS NULL
AND c.tanggal_putusan IS NULL");


$jum=0;
$noperkara='';
while($r=mysql_fetch_array($cek_pendaftaran)){
//$tanggal1  = tgl_indo($r['tanggal_pendaftaran']);
      $jum++;
	  $noperkara=$noperkara.$r['nomor_perkara'].'<br/>';
	  
  }
  
  	$breaks = array("<br />","<br>","<br/>");  
				$text = str_ireplace($breaks, "\r\n", $noperkara);
			//	echo $noperkara;
  $pesan="Info SIPP - Perkara Baru yang belum dilakukan penunjukan PMH sebanyak = $jum Perkara \r\nBerikut Nomor Perkara yang Belum PMH \r\n$text";
  if($jum>0){
 foreach($no_telpon_pimpinan as $a){
			
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim) values ('$a','$pesan','t',null,null)");	
		
		
  
  }
  }
		
			?>