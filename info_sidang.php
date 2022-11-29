
<?php
mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
//include 'function_app.php';	






?>


		<?php 
		
	$cek_pendaftaran=mysql_query("SELECT a.tanggal_penetapan, a.tanggal_sidang, b.perkara_id, b.nomor_perkara, b.tanggal_pendaftaran, c.majelis_hakim_id, c.majelis_hakim_text,c.panitera_pengganti_id, c.panitera_pengganti_text, c.jurusita_id, c.jurusita_text FROM sipp.perkara_penetapan_hari_sidang a INNER JOIN sipp.perkara b ON a.perkara_id=b.perkara_id INNER JOIN sipp.perkara_penetapan c ON a.perkara_id=c.perkara_id WHERE a.tanggal_penetapan='".date('Y-m-d')."'");
		//$cek_pendaftaran=mysql_query("SELECT a.tanggal_penetapan, a.tanggal_sidang, b.perkara_id, b.nomor_perkara, b.tanggal_pendaftaran, c.majelis_hakim_id, c.majelis_hakim_text,c.panitera_pengganti_id, c.panitera_pengganti_text, c.jurusita_id, c.jurusita_text FROM sipp.perkara_penetapan_hari_sidang a INNER JOIN sipp.perkara b ON a.perkara_id=b.perkara_id INNER JOIN sipp.perkara_penetapan c ON a.perkara_id=c.perkara_id WHERE a.tanggal_penetapan='2022-07-26'");
//echo "SELECT a.tanggal_penetapan, a.tanggal_sidang, b.perkara_id, b.nomor_perkara, b.tanggal_pendaftaran, c.majelis_hakim_id, c.majelis_hakim_nama,c.panitera_pengganti_id, c.panitera_pengganti_text, c.jurusita_id, c.jurusita_text FROM sipp.perkara_penetapan_hari_sidang a INNER JOIN sipp.perkara b ON a.perkara_id=b.perkara_id INNER JOIN sipp.perkara_penetapan c ON a.perkara_id=c.perkara_id WHERE a.tanggal_penetapan='".date('Y-m-d')."'";
//exit;
while($r=mysql_fetch_array($cek_pendaftaran)){

$tanggal_sidang  = tgl_indo($r['tanggal_sidang']);
//$km=explode(",",$r['majelis_hakim_id']);
$breaks = array("<br />","<br>","<br/>","</br>");  
				$majelis = str_ireplace($breaks, "\r\n", $r['majelis_hakim_text']);

$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='jurusita'");
while($kontak=mysql_fetch_array($cek_kontak)){
	$no_telpon[$kontak['idsipp']]=$kontak['nomorhp'];	
}

//notif jurusita
$a=$no_telpon[$r['jurusita_id']];
$pesan="Info SIPP - Sidang Perkara Nomor $r[nomor_perkara] Pada Tanggal $tanggal_sidang untuk Susunan Majelis \r\n".$majelis." \r\n".$r['panitera_pengganti_text']." \r\n".$r['jurusita_text'];
		
		//echo "select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '$r[tanggal_penetapan]'";
		$cek=mysql_query("select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '$r[tanggal_penetapan]'");
		$jum=mysql_num_rows($cek);
		if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_sidang (perkara_id, nomor_perkara,tanggal_penetapan,tanggal_sidang,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_penetapan]','$r[tanggal_sidang]','$pesan','$a','".date('Y-m-d H:i:s')."')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_penetapan = '$r[tanggal_penetapan]' and tanggal_sidang='$r[tanggal_sidang]' and notif_pesan ='$pesan' and nomor_hp='$a'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$a','$pesan','t',null,null,'$id[id_notif_sidang]')");	
			
		}

$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='pp' or jabatan='panitera'");
while($kontak=mysql_fetch_array($cek_kontak)){
	$no_telpon[$kontak['idsipp']]=$kontak['nomorhp'];	
}


$b=$no_telpon[$r['panitera_pengganti_id']];
//$pesan="Info SIPP - Sidang Perkara Nomor $r[nomor_perkara] Pada Tanggal $tanggal_sidang untuk Susunan Majelis \r\n".$majelis." \r\n".$r['panitera_pengganti_text']." \r\n".$r['jurusita_text'];
		
		//echo "select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '$r[tanggal_penetapan]'";
		$cek=mysql_query("select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$b' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '$r[tanggal_penetapan]'");
		$jum=mysql_num_rows($cek);
		if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_sidang (perkara_id, nomor_perkara,tanggal_penetapan,tanggal_sidang,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_penetapan]','$r[tanggal_sidang]','$pesan','$b','".date('Y-m-d H:i:s')."')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_penetapan = '$r[tanggal_penetapan]' and tanggal_sidang='$r[tanggal_sidang]' and notif_pesan ='$pesan' and nomor_hp='$b'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$b','$pesan','t',null,null,'$id[id_notif_sidang]')");	
			
		}


$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='wakil' or jabatan='hakim'");
while($kontak=mysql_fetch_array($cek_kontak)){
	$no_telpon[$kontak['idsipp']]=$kontak['nomorhp'];	
}

$km=explode(",",$r['majelis_hakim_id']);

$c=$no_telpon[$km[1]];
//$pesan="Info SIPP - Sidang Perkara Nomor $r[nomor_perkara] Pada Tanggal $tanggal_sidang untuk Susunan Majelis \r\n".$majelis." \r\n".$r['panitera_pengganti_text']." \r\n".$r['jurusita_text'];
	if (!empty($c)){
		//echo "select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '$r[tanggal_penetapan]'";
		$cek=mysql_query("select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$c' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '$r[tanggal_penetapan]'");
		//echo "select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$c' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '$r[tanggal_penetapan]'";
		$jum=mysql_num_rows($cek);
		//echo $jum;exit;
		if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_sidang (perkara_id, nomor_perkara,tanggal_penetapan,tanggal_sidang,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_penetapan]','$r[tanggal_sidang]','$pesan','$c','".date('Y-m-d H:i:s')."')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_penetapan = '$r[tanggal_penetapan]' and tanggal_sidang='$r[tanggal_sidang]' and notif_pesan ='$pesan' and nomor_hp='$c'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$c','$pesan','t',null,null,'$id[id_notif_sidang]')");	
			
		}	
}

$d=$no_telpon[$km[2]];
//$pesan="Info SIPP - Sidang Perkara Nomor $r[nomor_perkara] Pada Tanggal $tanggal_sidang untuk Susunan Majelis \r\n".$majelis." \r\n".$r['panitera_pengganti_text']." \r\n".$r['jurusita_text'];
	if (!empty($d)){
		//echo "select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '$r[tanggal_penetapan]'";
		$cek=mysql_query("select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$d' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '$r[tanggal_penetapan]'");
		$jum=mysql_num_rows($cek);
		if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_sidang (perkara_id, nomor_perkara,tanggal_penetapan,tanggal_sidang,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_penetapan]','$r[tanggal_sidang]','$pesan','$d','".date('Y-m-d H:i:s')."')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_penetapan = '$r[tanggal_penetapan]' and tanggal_sidang='$r[tanggal_sidang]' and notif_pesan ='$pesan' and nomor_hp='$d'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$d','$pesan','t',null,null,'$id[id_notif_sidang]')");	
			
		}	
}

  }		
  
  mysql_close();
		
			?>