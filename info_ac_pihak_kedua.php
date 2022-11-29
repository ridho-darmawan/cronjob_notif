
<?php
mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
//include 'function_app.php';	




?>


		<?php 
		
		$cek_ac=mysql_query("SELECT a.nomor_perkara,
									a.perkara_id,
                                    a.jenis_perkara_text,
                                    a.pihak1_text, 
                                    a.pihak2_text, 
                                    a.tahapan_terakhir_id,
                                    a.tahapan_terakhir_text,
                                    b.tanggal_putusan,
                                    b.tanggal_bht,
                                    b.status_putusan_id,
                                    g.nomor_akta_cerai,
                                    g.no_seri_akta_cerai,
                                    g.tgl_akta_cerai,
                                    g.tgl_penyerahan_akta_cerai,
                                    g.tgl_penyerahan_akta_cerai_pihak2,
                                    g.akta_cerai_dok,
                                    h.pihak_id,
				    				i.telepon
                                    FROM sipp.perkara a, sipp.perkara_putusan b, sipp.hakim_pn c, sipp.panitera_pn d, sipp.perkara_panitera_pn e, sipp.perkara_hakim_pn f, sipp.perkara_akta_cerai g, sipp.perkara_pihak2 h, sipp.pihak i
                                    WHERE b.status_putusan_id = 62
                                    AND a.perkara_id = h.perkara_id
				    				AND h.pihak_id = i.id
                                    AND g.tgl_akta_cerai IS NOT NULL
                                    AND g.akta_cerai_dok IS NOT NULL
                                    AND g.tgl_penyerahan_akta_cerai_pihak2 IS NULL
                                    AND i.telepon != ''
                                    AND a.tahapan_terakhir_id < 20
                                    AND a.perkara_id = b.perkara_id 
                                    AND a.perkara_id = g.perkara_id 
                                    AND a.jenis_perkara_id = '347'
                                    AND f.perkara_id = a.perkara_id 
                                    AND e.perkara_id = a.perkara_id 
                                    AND f.hakim_id = c.id 
                                    AND e.panitera_id = d.id 
                                    AND f.urutan = '1'
                                    AND e.aktif = 'Y'
                                    AND f.aktif = 'Y'
                                    AND b.tanggal_bht >= CURDATE()");
                                    // AND b.tanggal_bht > '2022-06-29'");
while($r=mysql_fetch_array($cek_ac)){

$tanggal_ac  = tgl_indo($r['tgl_akta_cerai']);

		// $amar_putusan = htmlspecialchars_decode($r['amar_putusan']);
		$pesan = "*Info Perkara PA Tembilahan* - Akta Cerai $r[nomor_perkara] terbit tgl $tanggal_ac no $r[nomor_akta_cerai] dikirim otomatis oleh PA. Tembilahan";


		//echo "select *  from notif_perkara_pa.notif_pendaftaran_perkara where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a'";echo "<br/>";
		$cek=mysql_query("select *  from notif_perkara_pa.notif_ac_pihak_kedua where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$r[telepon]'");
		$jum=mysql_num_rows($cek);
		if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_ac_pihak_kedua (perkara_id, nomor_perkara,tanggal_ac,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tgl_akta_cerai]','$pesan','$r[telepon]','".date('Y-m-d H:i:s')."')");

		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_ac_pihak_kedua where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_ac = '$r[tgl_akta_cerai]' and notif_pesan ='$pesan' and nomor_hp='$r[telepon]'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$r[telepon]','$pesan','t',null,null,'$id[id_notif_ac_pihak_kedua]')");	
		//exit;	
		}
		
  
  }		
  
  mysql_close();
		
			?>