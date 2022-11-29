
<?php
mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
//include 'function_app.php';	




?>


		<?php 
		
		$cek_amar=mysql_query("SELECT a.perkara_id,
			b.nomor_perkara, 
			b.tanggal_pendaftaran,
			b.jenis_perkara_text,
			a.tanggal_putusan, 
			a.amar_putusan, 
			a.amar_putusan_dok, 
			b.para_pihak, 
			b.pihak1_text,
			b.pihak2_text,
			c.pihak_id AS pihak_id_pihak1,
			g.pihak_id AS pihak_id_pihak2,
			d.telepon AS telepon_pihak1,
			h.telepon AS telepon_pihak2,
			e.nama AS nama_pengacara,
			i.nama AS nama_pengacara_pihak2,
			e.pihak_id AS pihak_id_pengacara,
			i.pihak_id AS pihak_id_pengacara_pihak2,
			f.telepon AS telepon_pengacara,
			j.telepon AS telepon_pengacara_pihak2
			FROM sipp.perkara_putusan a 
			LEFT JOIN sipp.perkara b ON b.perkara_id = a.perkara_id 
			LEFT JOIN sipp.perkara_pihak1 c ON c.perkara_id = a.perkara_id 
			LEFT JOIN sipp.pihak d ON d.id = c.pihak_id
			LEFT JOIN sipp.perkara_pengacara e ON e.perkara_id = c.perkara_id
			LEFT JOIN sipp.pihak f ON f.id = e.pengacara_id
			LEFT JOIN sipp.perkara_pihak2 g ON g.perkara_id = a.perkara_id
			LEFT JOIN sipp.pihak h ON h.id = g.pihak_id
			LEFT JOIN sipp.perkara_pengacara i ON i.perkara_id = g.perkara_id
			LEFT JOIN sipp.pihak j ON j.id = i.pengacara_id
			WHERE a.amar_putusan_dok IS NOT NULL
			AND a.amar_putusan IS NOT NULL
			AND a.tanggal_putusan IS NOT NULL
			AND a.tanggal_putusan >= CURDATE()
			-- AND a.tanggal_putusan >= '2022-07-21'
			GROUP BY a.perkara_id DESC
			ORDER BY b.perkara_id DESC");

				

while($r=mysql_fetch_array($cek_amar)){

	

$tanggal_putusan  = tgl_indo($r['tanggal_putusan']);
$breaks = array("<br />","<br>","<br/>","</br>");  
$para_pihak = str_ireplace($breaks, "\r\n", $r['para_pihak']);

$break_amar  =array("</li>","<ol>","</ol>", "<strong>","</strong>");  
$break_amar2  =array("<li>","<li >");  
$break_tanda = array("&#39;");
$amar_putusan_break = str_ireplace($break_amar, "", $r['amar_putusan']);
$amar_putusan_replace = str_ireplace($break_amar2,"\r\n-",$amar_putusan_break);
$amar_putusan = str_ireplace($break_tanda,"`",$amar_putusan_replace);

// $amar_putusan = htmlspecialchars_decode($r['amar_putusan']);

	$pesan="*Info Perkara PA Tembilahan* - Perkara $r[jenis_perkara_text] atas nama\r\n$para_pihak \r\n\r\nNomor Perkara $r[nomor_perkara] telah putus pada tanggal $tanggal_putusan dengan amar putusan sebagai berikut:$amar_putusan\r\n*Catatan:*\r\nBiaya perkara di atas telah dibayarkan pada saat pendaftaran perkara dan pihak berperkara tidak perlu mengeluarkan biaya apapun atas informasi ini.\r\n\r\nUntuk salinan putusan telah tersedia dan silakan untuk diambil pada bagian pelayanan Pengadilan Agama Tembilahan. ";

		// notif ke pihak 1

		if (!empty($r['telepon_pihak1'])) {

			$cek=mysql_query("select *  from notif_perkara_pa.notif_amar_putusan where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$r[telepon_pihak1]'");
			
			$jum=mysql_num_rows($cek);

			if($jum<1){
			
				$insert=mysql_query("insert into notif_perkara_pa.notif_amar_putusan (perkara_id, nomor_perkara,tanggal_pendaftaran,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_pendaftaran]','$pesan','$r[telepon_pihak1]','".date('Y-m-d H:i:s')."')");

				$insert_ok=mysql_query("select * from notif_perkara_pa.notif_amar_putusan where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_pendaftaran = '$r[tanggal_pendaftaran]' and notif_pesan ='$pesan' and nomor_hp='$r[telepon_pihak1]'");

				$id=mysql_fetch_array($insert_ok);	

				$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$r[telepon_pihak1]','$pesan','t',null,null,'$id[id_notif_amar_putusan]')");	
		
			}
		}

		// akhir notif pihak 1

		// notif ke pihak1 pengacara

		if (!empty($r['telepon_pengacara'])) {

			$cek=mysql_query("select *  from notif_perkara_pa.notif_amar_putusan where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$r[telepon_pengacara]'");
			
			$jum=mysql_num_rows($cek);

			if($jum<1){
			
				$insert=mysql_query("insert into notif_perkara_pa.notif_amar_putusan (perkara_id, nomor_perkara,tanggal_pendaftaran,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_pendaftaran]','$pesan','$r[telepon_pengacara]','".date('Y-m-d H:i:s')."')");

				$insert_ok=mysql_query("select * from notif_perkara_pa.notif_amar_putusan where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_pendaftaran = '$r[tanggal_pendaftaran]' and notif_pesan ='$pesan' and nomor_hp='$r[telepon_pengacara]'");

				$id=mysql_fetch_array($insert_ok);	

				$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$r[telepon_pengacara]','$pesan','t',null,null,'$id[id_notif_amar_putusan]')");	
		
			}
		}

		// akhir notif pihak 1 pengacara

		
		// notif ke pihak 2

		if (!empty($r['telepon_pihak2'])) {

			$cek=mysql_query("select *  from notif_perkara_pa.notif_amar_putusan where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$r[telepon_pihak2]'");
			
			$jum=mysql_num_rows($cek);

			if($jum<1){
			
				$insert=mysql_query("insert into notif_perkara_pa.notif_amar_putusan (perkara_id, nomor_perkara,tanggal_pendaftaran,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_pendaftaran]','$pesan','$r[telepon_pihak2]','".date('Y-m-d H:i:s')."')");

				$insert_ok=mysql_query("select * from notif_perkara_pa.notif_amar_putusan where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_pendaftaran = '$r[tanggal_pendaftaran]' and notif_pesan ='$pesan' and nomor_hp='$r[telepon_pihak2]'");

				$id=mysql_fetch_array($insert_ok);	

				$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$r[telepon_pihak2]','$pesan','t',null,null,'$id[id_notif_amar_putusan]')");	
		
			}
		}

		// akhir notif pihak 2

		// notif ke pihak2 pengacara

		if (!empty($r['telepon_pengacara_pihak2'])) {

			$cek=mysql_query("select *  from notif_perkara_pa.notif_amar_putusan where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$r[telepon_pengacara_pihak2]'");
			
			$jum=mysql_num_rows($cek);

			if($jum<1){
			
				$insert=mysql_query("insert into notif_perkara_pa.notif_amar_putusan (perkara_id, nomor_perkara,tanggal_pendaftaran,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_pendaftaran]','$pesan','$r[telepon_pengacara_pihak2]','".date('Y-m-d H:i:s')."')");

				$insert_ok=mysql_query("select * from notif_perkara_pa.notif_amar_putusan where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_pendaftaran = '$r[tanggal_pendaftaran]' and notif_pesan ='$pesan' and nomor_hp='$r[telepon_pengacara_pihak2]'");

				$id=mysql_fetch_array($insert_ok);	

				$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$r[telepon_pengacara_pihak2]','$pesan','t',null,null,'$id[id_notif_amar_putusan]')");	
		
			}
		}

		// akhir notif pihak 2 pengacara

		
		
  
  }		
  
  mysql_close();
		
			?>