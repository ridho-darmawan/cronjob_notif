
<?php
mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
//include 'function_app.php';	






?>


		<?php 
		
	$cek_pendaftaran=mysql_query("SELECT b.perkara_id,b.nomor_perkara, a.tanggal_sidang, a.agenda, c.jurusita_id, c.jurusita_text FROM sipp.perkara_jadwal_sidang a INNER JOIN sipp.perkara b ON b.perkara_id=a.perkara_id INNER JOIN sipp.perkara_penetapan c ON c.perkara_id=a.perkara_id WHERE a.urutan<>1 AND DATE(a.diinput_tanggal)='".date('Y-m-d')."'" );
		//$cek_pendaftaran=mysql_query("SELECT a.tanggal_penetapan, a.tanggal_sidang, b.perkara_id, b.nomor_perkara, b.tanggal_pendaftaran, c.majelis_hakim_id, c.majelis_hakim_text,c.panitera_pengganti_id, c.panitera_pengganti_text, c.jurusita_id, c.jurusita_text FROM sipp.perkara_penetapan_hari_sidang a INNER JOIN sipp.perkara b ON a.perkara_id=b.perkara_id INNER JOIN sipp.perkara_penetapan c ON a.perkara_id=c.perkara_id WHERE a.tanggal_penetapan='2022-07-26'");
//echo "SELECT a.tanggal_penetapan, a.tanggal_sidang, b.perkara_id, b.nomor_perkara, b.tanggal_pendaftaran, c.majelis_hakim_id, c.majelis_hakim_nama,c.panitera_pengganti_id, c.panitera_pengganti_text, c.jurusita_id, c.jurusita_text FROM sipp.perkara_penetapan_hari_sidang a INNER JOIN sipp.perkara b ON a.perkara_id=b.perkara_id INNER JOIN sipp.perkara_penetapan c ON a.perkara_id=c.perkara_id WHERE a.tanggal_penetapan='".date('Y-m-d')."'";
//exit;
while($r=mysql_fetch_array($cek_pendaftaran)){

$tanggal_sidang  = tgl_indo($r['tanggal_sidang']);

$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='jurusita'");
while($kontak=mysql_fetch_array($cek_kontak)){
	$no_telpon[$kontak['idsipp']]=$kontak['nomorhp'];	
}

//notif jurusita
$a=$no_telpon[$r['jurusita_id']];
$pesan="Info SIPP - (*Tundaan Sidang*) Perkara Nomor $r[nomor_perkara] Sidang Berikutnya pada Tanggal $tanggal_sidang dengan agenda $r[agenda]";;
		
		//echo "select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '$r[tanggal_penetapan]'";
		$cek=mysql_query("select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '".date('Y-m-d')."'");
		$jum=mysql_num_rows($cek);
		if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_sidang (perkara_id, nomor_perkara,tanggal_penetapan,tanggal_sidang,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','".date('Y-m-d')."','$r[tanggal_sidang]','$pesan','$a','".date('Y-m-d H:i:s')."')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_penetapan = '".date('Y-m-d')."' and tanggal_sidang='$r[tanggal_sidang]' and notif_pesan ='$pesan' and nomor_hp='$a'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$a','$pesan','t',null,null,'$id[id_notif_sidang]')");	
			
		}


  }		
  
  mysql_close();
		
			?>