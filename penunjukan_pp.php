
<?php
mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
//include 'function_app.php';	


$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='panitera'");
$kontak=mysql_fetch_array($cek_kontak);
	$no_panitera=$kontak['nomorhp'];	




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
b.majelis_hakim_text,
c.tanggal_putusan
FROM sipp.perkara a
LEFT JOIN sipp.perkara_penetapan b ON a.perkara_id = b.perkara_id
LEFT JOIN sipp.perkara_putusan c ON a.perkara_id = c.perkara_id 	
WHERE a.tanggal_pendaftaran IS NOT NULL
AND a.tahapan_terakhir_id > 10
AND b.penetapan_majelis_hakim IS NOT NULL
AND b.penetapan_panitera_pengganti IS NULL
AND c.tanggal_putusan IS NULL");



while($r=mysql_fetch_array($cek_pendaftaran)){

$tanggal1  = tgl_indo($r['tanggal_pendaftaran']);
//$km=explode(",",$r['majelis_hakim_id']);
$a=$no_panitera;
$pesan="Info SIPP - Perkara Baru Nomor $r[nomor_perkara] Tanggal Pendaftaran $tanggal1 untuk segera dibuatkan Penunjukan Panitera Pengganti dan Jurusita Pengganti";
		//echo "select *  from notif_perkara_pa.notif_pendaftaran_perkara where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a'";echo "<br/>";
		$cek=mysql_query("select *  from notif_perkara_pa.notif_pp where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a'");
		$jum=mysql_num_rows($cek);
		if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_pp (perkara_id, nomor_perkara,tanggal_pendaftaran,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_pendaftaran]','$pesan','$a','".date('Y-m-d H:i:s')."')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_pp where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_pendaftaran = '$r[tanggal_pendaftaran]' and notif_pesan ='$pesan' and nomor_hp='$a'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$a','$pesan','t',null,null,'$id[id_penunjukanpp]')");	
		//exit;	
		}
		
  
  }		
  
  mysql_close();
		
			?>