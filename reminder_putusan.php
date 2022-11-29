
<?php
mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
//include 'function_app.php';	




?>


		<?php 
		
		$cek_pendaftaran=mysql_query("SELECT sipp.perkara.perkara_id, 
sipp.perkara.nomor_perkara, 
sipp.perkara.jenis_perkara_text,
sipp.perkara.proses_terakhir_text, 
sipp.perkara.pihak1_text, 
sipp.perkara.tanggal_pendaftaran, 
sipp.perkara_putusan.tanggal_putusan,
sipp.perkara_putusan.tanggal_bht,
sipp.status_putusan.id, sipp.status_putusan.nama AS status_putusan_nama, 
sipp.hakim_pn.nama_gelar AS nama_ketua_majelis,
sipp.perkara_hakim_pn.hakim_id,
sipp.panitera_pn.nama_gelar AS nama_pp,
sipp.perkara_panitera_pn.panitera_id,
sipp.perkara_putusan.diinput_oleh AS konseptor
FROM sipp.perkara, sipp.perkara_putusan, sipp.hakim_pn, sipp.perkara_hakim_pn, sipp.panitera_pn, sipp.perkara_panitera_pn, sipp.status_putusan
WHERE sipp.perkara.perkara_id = sipp.perkara_putusan.perkara_id
AND sipp.perkara_hakim_pn.hakim_id = sipp.hakim_pn.id 
AND sipp.perkara_hakim_pn.perkara_id = sipp.perkara_putusan.perkara_id 
AND sipp.perkara_panitera_pn.panitera_id = sipp.panitera_pn.id 
AND sipp.perkara_panitera_pn.perkara_id = sipp.perkara_putusan.perkara_id
AND sipp.perkara_putusan.status_putusan_id = sipp.status_putusan.id
AND sipp.perkara_putusan.tanggal_putusan IS NOT NULL
AND sipp.perkara_putusan.amar_putusan_dok IS NULL
AND sipp.perkara_putusan.tanggal_putusan NOT LIKE '%2014%'
AND sipp.perkara_putusan.tanggal_putusan NOT LIKE '%2015%'
GROUP BY sipp.perkara_putusan.perkara_id DESC
ORDER BY sipp.perkara.perkara_id DESC");
//echo "SELECT a.tanggal_penetapan, a.tanggal_sidang, b.perkara_id, b.nomor_perkara, b.tanggal_pendaftaran, c.majelis_hakim_id, c.majelis_hakim_nama,c.panitera_pengganti_id, c.panitera_pengganti_text, c.jurusita_id, c.jurusita_text FROM sipp.perkara_penetapan_hari_sidang a INNER JOIN sipp.perkara b ON a.perkara_id=b.perkara_id INNER JOIN sipp.perkara_penetapan c ON a.perkara_id=c.perkara_id WHERE a.tanggal_penetapan='".date('Y-m-d')."'";

while($r=mysql_fetch_array($cek_pendaftaran)){
$tanggal_putus  = tgl_indo($r['tanggal_putusan']);


$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='pp' or jabatan='panitera'");
while($kontak=mysql_fetch_array($cek_kontak)){
	$no_telpon[$kontak['idsipp']]=$kontak['nomorhp'];	
}


//echo "$tanggal_sidang";
$a=$no_telpon[$r['panitera_id']];
$breaks = array("<a/>","<a>","</a>");  
				$waktu = str_ireplace($breaks, "", $r['waktu']);
$pesan="Info SIPP - Putusan Belum Dimasukkan ke SIPP Perkara Nomor $r[nomor_perkara] Tanggal Putus $tanggal_putus Ketua Majelis $r[nama_ketua_majelis], Panitera Pengganti $r[nama_pp]";

//echo $pesan;exit;		
//echo $a. //$pesan;exit;		
		//echo "select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '$r[tanggal_penetapan]'";
		//$cek=mysql_query("select *  from notif_perkara_pa.notif_blmtunda_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[waktu_sidang]' and nama_penerima='$r[panitera_pengganti]'");
	//	echo "select *  from notif_perkara_pa.notif_blmtunda_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[waktu_sidang]' and nama_penerima='$r[panitera_pengganti]'";
	//	exit;
		//$jum=mysql_num_rows($cek);
		//if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_blumupload_putusan (perkara_id, nomor_perkara,tanggal_putus,notif_pesan,nomor_hp, date_input, nama_penerima) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_putusan]','$pesan','$a','".date('Y-m-d H:i:s')."','$r[nama_pp]')");
	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_blumupload_putusan where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_putus = '$r[tanggal_putusan]' and notif_pesan ='$pesan' and nomor_hp='$a' and nama_penerima='$r[nama_pp]'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$a','$pesan','t',null,null,'$id[id_blmupload_putusan]')");	
			
		//}

$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='hakim' or jabatan='ketua' or jabatan='wakil'");
while($kontak=mysql_fetch_array($cek_kontak)){
	$no_telpon[$kontak['idsipp']]=$kontak['nomorhp'];	
}

$a=$no_telpon[$r['hakim_id']];

	//$cek=mysql_query("select *  from notif_perkara_pa.notif_blmtunda_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[waktu_sidang]' and nama_penerima='$r[ketua_majelis]'");
	//	echo "select *  from notif_perkara_pa.notif_blmtunda_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[waktu_sidang]' and nama_penerima='$r[panitera_pengganti]'";
	//	exit;
		//$jum=mysql_num_rows($cek);
		//if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_blumupload_putusan (perkara_id, nomor_perkara,tanggal_putus,notif_pesan,nomor_hp, date_input, nama_penerima) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_putusan]','$pesan','$a','".date('Y-m-d H:i:s')."','$r[nama_ketua_majelis]')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_blumupload_putusan where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_putus = '$r[tanggal_putusan]' and notif_pesan ='$pesan' and nomor_hp='$a' and nama_penerima='$r[nama_ketua_majelis]'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$a','$pesan','t',null,null,'$id[id_blmupload_putusan]')");	
			
		//}
	$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='ketua' or jabatan='wakil'");
while($kontak=mysql_fetch_array($cek_kontak)){
	$no_telpon_pimpinan[]=$kontak['nomorhp'];	
	
	$a=$kontak['nomorhp'];

	//$cek=mysql_query("select *  from notif_perkara_pa.notif_blmtunda_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[waktu_sidang]' and nama_penerima='$r[ketua_majelis]'");
	//	echo "select *  from notif_perkara_pa.notif_blmtunda_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[waktu_sidang]' and nama_penerima='$r[panitera_pengganti]'";
	//	exit;
		//$jum=mysql_num_rows($cek);
		//if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_blumupload_putusan (perkara_id, nomor_perkara,tanggal_putus,notif_pesan,nomor_hp, date_input, nama_penerima) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_putusan]','$pesan','$a','".date('Y-m-d H:i:s')."','$kontak[nama]')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_blumupload_putusan where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_putus = '$r[tanggal_putusan]' and notif_pesan ='$pesan' and nomor_hp='$a' and nama_penerima='$kontak[nama]'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$a','$pesan','t',null,null,'$id[id_blmupload_putusan]')");	
	
}


		
		
  }		
  
  mysql_close();
		
			?>