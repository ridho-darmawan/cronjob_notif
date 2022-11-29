
<?php
mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
include 'function_app.php';	






?>


		<?php 
		
		$cek_pendaftaran=mysql_query("SELECT sipp.perkara.perkara_id,
sipp.perkara.alur_perkara_id, 
sipp.perkara.nomor_perkara,
sipp.perkara.pihak1_text,
sipp.perkara.pihak2_text,
sipp.perkara.para_pihak,
sipp.perkara.proses_terakhir_text,
sipp.perkara_jadwal_sidang.tanggal_sidang as waktu_sidang, 
sipp.perkara_jadwal_sidang.sidang_keliling,
sipp.perkara_jadwal_sidang.keterangan,
(CASE WHEN (SELECT TIMESTAMPDIFF(HOUR, sipp.perkara_jadwal_sidang.tanggal_sidang,CURRENT_DATE)) <= '24' THEN 'DALAM 24 JAM' ELSE '<a>LEBIH DARI 24 JAM</a>' END) AS waktu,
(GROUP_CONCAT(DISTINCT DATE_FORMAT(sipp.perkara_jadwal_sidang.tanggal_sidang, '%d-%m-%Y') ,' | ', sipp.perkara_jadwal_sidang.agenda ORDER BY sipp.perkara_jadwal_sidang.tanggal_sidang ASC SEPARATOR '<br>')) AS tanggal_sidang, 
sipp.hakim_pn.nama_gelar AS ketua_majelis,
sipp.hakim_pn.keterangan AS phone_hakim, 
sipp.perkara_hakim_pn.hakim_id,
sipp.panitera_pn.nama_gelar AS panitera_pengganti,
sipp.perkara_panitera_pn.panitera_id,
sipp.panitera_pn.keterangan AS phone
FROM  sipp.perkara, sipp.perkara_jadwal_sidang, sipp.hakim_pn, sipp.panitera_pn, sipp.perkara_panitera_pn, sipp.perkara_hakim_pn
WHERE sipp.perkara_jadwal_sidang.perkara_id = sipp.perkara.perkara_id 
AND sipp.perkara_panitera_pn.perkara_id = sipp.perkara_jadwal_sidang.perkara_id
AND sipp.perkara_panitera_pn.panitera_id = sipp.panitera_pn.id
AND sipp.perkara_hakim_pn.perkara_id = sipp.perkara_jadwal_sidang.perkara_id
AND sipp.perkara_hakim_pn.hakim_id = sipp.hakim_pn.id
AND sipp.perkara_hakim_pn.urutan = 1
AND sipp.perkara_jadwal_sidang.ditunda ='T'
AND (sipp.perkara_jadwal_sidang.keterangan IS NULL OR sipp.perkara_jadwal_sidang.keterangan ='' OR sipp.perkara_jadwal_sidang.keterangan = '0' OR  sipp.perkara_jadwal_sidang.keterangan LIKE '%kantor%')
AND sipp.perkara_jadwal_sidang.tanggal_sidang <= NOW()
AND (sipp.perkara.proses_terakhir_text = 'persidangan' OR sipp.perkara.proses_terakhir_text LIKE '%pertama%'OR sipp.perkara.proses_terakhir_text LIKE '%penetapan hari sidang ikrar talak%') 
GROUP BY sipp.perkara_jadwal_sidang.perkara_id
ORDER BY sipp.hakim_pn.nama_gelar DESC");
//echo "SELECT a.tanggal_penetapan, a.tanggal_sidang, b.perkara_id, b.nomor_perkara, b.tanggal_pendaftaran, c.majelis_hakim_id, c.majelis_hakim_nama,c.panitera_pengganti_id, c.panitera_pengganti_text, c.jurusita_id, c.jurusita_text FROM sipp.perkara_penetapan_hari_sidang a INNER JOIN sipp.perkara b ON a.perkara_id=b.perkara_id INNER JOIN sipp.perkara_penetapan c ON a.perkara_id=c.perkara_id WHERE a.tanggal_penetapan='".date('Y-m-d')."'";

while($r=mysql_fetch_array($cek_pendaftaran)){

$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='pp' or jabatan='panitera'");
while($kontak=mysql_fetch_array($cek_kontak)){
	$no_telpon[$kontak['idsipp']]=$kontak['nomorhp'];	
}

$tanggal_sidang  = tgl_indo($r['waktu_sidang']);

//echo "$tanggal_sidang";
$a=$no_telpon[$r['panitera_id']];
$breaks = array("<a/>","<a>","</a>");  
				$waktu = str_ireplace($breaks, "", $r['waktu']);
$pesan="Info SIPP - Tundaan Sidang Belum Dimasukkan ke SIPP Perkara Nomor $r[nomor_perkara] Tanggal Sidang Terakhir $tanggal_sidang (Status Waktu = *$waktu*) Ketua Majelis $r[ketua_majelis], Panitera Pengganti $r[panitera_pengganti]";

//echo $pesan;. //$pesan;exit;		
//echo $a. //$pesan;exit;		
		//echo "select *  from notif_perkara_pa.notif_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[tanggal_sidang]' and tanggal_penetapan = '$r[tanggal_penetapan]'";
		$cek=mysql_query("select *  from notif_perkara_pa.notif_blmtunda_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[waktu_sidang]' and nama_penerima='$r[panitera_pengganti]'");
	//	echo "select *  from notif_perkara_pa.notif_blmtunda_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[waktu_sidang]' and nama_penerima='$r[panitera_pengganti]'";
	//	exit;
		$jum=mysql_num_rows($cek);
		
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_blmtunda_sidang (perkara_id, nomor_perkara,tanggal_sidang,notif_pesan,nomor_hp, date_input, nama_penerima) values ('$r[perkara_id]','$r[nomor_perkara]','$r[waktu_sidang]','$pesan','$a','".date('Y-m-d H:i:s')."','$r[panitera_pengganti]')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_blmtunda_sidang where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_sidang = '$r[waktu_sidang]' and notif_pesan ='$pesan' and nomor_hp='$a' and nama_penerima='$r[panitera_pengganti]'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$a','$pesan','t',null,null,'$id[id_notif_blm_tunda]')");	
			
		
$cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='hakim' or jabatan='wakil' or jabatan='ketua'");
while($kontak=mysql_fetch_array($cek_kontak)){
	$no_telpon[$kontak['idsipp']]=$kontak['nomorhp'];	
}

$a=$no_telpon[$r['hakim_id']];

	$cek=mysql_query("select *  from notif_perkara_pa.notif_blmtunda_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[waktu_sidang]' and nama_penerima='$r[ketua_majelis]'");
	//	echo "select *  from notif_perkara_pa.notif_blmtunda_sidang where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a' and tanggal_sidang = '$r[waktu_sidang]' and nama_penerima='$r[panitera_pengganti]'";
	//	exit;
		$jum=mysql_num_rows($cek);
		
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_blmtunda_sidang (perkara_id, nomor_perkara,tanggal_sidang,notif_pesan,nomor_hp, date_input, nama_penerima) values ('$r[perkara_id]','$r[nomor_perkara]','$r[waktu_sidang]','$pesan','$a','".date('Y-m-d H:i:s')."','$r[ketua_majelis]')");	
		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_blmtunda_sidang where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_sidang = '$r[waktu_sidang]' and notif_pesan ='$pesan' and nomor_hp='$a' and nama_penerima='$r[ketua_majelis]'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$a','$pesan','t',null,null,'$id[id_notif_blm_tunda]')");	
			
		
  }		
  
  mysql_close();
		
			?>