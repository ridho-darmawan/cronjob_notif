
<?php
mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server



$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='ketua' or jabatan='wakil' or jabatan='hakim'");
while($kontak=mysql_fetch_array($cek_kontak)){
	$no_telpon_hakim[$kontak['idsipp']]=$kontak['nomorhp'];	
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
$breaks = array("<br />","<br>","<br/>","</br>");  
				$majelis = str_ireplace($breaks, "\r\n", $r['majelis_hakim_text']);

$tanggal1  = tgl_indo($r['tanggal_pendaftaran']);
$km=explode(",",$r['majelis_hakim_id']);
$a=$no_telpon_hakim[$km[0]];
$pesan="Info SIPP - Perkara Baru Nomor $r[nomor_perkara] Tanggal Pendaftaran $tanggal1 untuk segera dibuatkan Penetapan Hari Sidang(PHS) \r\nBerikut Susunan Majelis Hakim \r\n$majelis";
		//echo "select *  from notif_perkara_pa.notif_pendaftaran_perkara where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a'";echo "<br/>";
		$cek=mysql_query("select *  from notif_perkara_pa.notif_perkara_phs where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a'");
		$jum=mysql_num_rows($cek);
		if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_perkara_phs (perkara_id, nomor_perkara,tanggal_pendaftaran,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_pendaftaran]','$pesan','$a','".date('Y-m-d H:i:s')."')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_perkara_phs where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_pendaftaran = '$r[tanggal_pendaftaran]' and notif_pesan ='$pesan' and nomor_hp='$a'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$a','$pesan','t',null,null,'$id[id_notif_phs]')");	
		//exit;	
		}
		
$b=$no_telpon_hakim[$km[1]];
if (!empty($b)){
$pesan="Info SIPP - Perkara Baru Nomor $r[nomor_perkara] Tanggal Pendaftaran $tanggal1 untuk Majelis Hakim\r\n$majelis";
		//echo "select *  from notif_perkara_pa.notif_pendaftaran_perkara where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a'";echo "<br/>";
		$cek=mysql_query("select *  from notif_perkara_pa.notif_perkara_phs where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$b'");
		$jum=mysql_num_rows($cek);
		if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_perkara_phs (perkara_id, nomor_perkara,tanggal_pendaftaran,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_pendaftaran]','$pesan','$b','".date('Y-m-d H:i:s')."')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_perkara_phs where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_pendaftaran = '$r[tanggal_pendaftaran]' and notif_pesan ='$pesan' and nomor_hp='$b'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$b','$pesan','t',null,null,'$id[id_notif_phs]')");	
		//exit;	
		}
  
}


$c=$no_telpon_hakim[$km[2]];
if (!empty($c)){
$pesan="Info SIPP - Perkara Baru Nomor $r[nomor_perkara] Tanggal Pendaftaran $tanggal1 untuk Majelis Hakim\r\n$majelis";
		//echo "select *  from notif_perkara_pa.notif_pendaftaran_perkara where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a'";echo "<br/>";
		$cek=mysql_query("select *  from notif_perkara_pa.notif_perkara_phs where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$c'");
		$jum=mysql_num_rows($cek);
		if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_perkara_phs (perkara_id, nomor_perkara,tanggal_pendaftaran,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_pendaftaran]','$pesan','$c','".date('Y-m-d H:i:s')."')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_perkara_phs where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_pendaftaran = '$r[tanggal_pendaftaran]' and notif_pesan ='$pesan' and nomor_hp='$c'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$c','$pesan','t',null,null,'$id[id_notif_phs]')");	
		//exit;	
		}
  
}
  
  }		
  
  mysql_close();
		
			?>