
<?php
mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
//include 'function_app.php';	




?>


		<?php 
		
		$cek_biaya_panjar=mysql_query("SELECT a.id,
                            a.perkara_id,
                            a.jenis_transaksi,
                            a.tanggal_transaksi,
                            a.uraian,
                            a.jumlah,
                            b.nomor_perkara,
                            c.pihak_id,
                            c.nama,
                            d.telepon
                            FROM sipp.perkara_biaya a, sipp.perkara b, sipp.perkara_pihak1 c, sipp.pihak d
                            WHERE a.jenis_transaksi = '1'
                            AND a.perkara_id = b.perkara_id
                            AND a.perkara_id = c.perkara_id
                            AND c.pihak_id = d.id
                            AND a.tanggal_transaksi  >= CURDATE()
                            GROUP BY a.perkara_id
                            ");

while($r=mysql_fetch_array($cek_biaya_panjar)){

$tanggal_transaksi  = tgl_indo($r['tanggal_transaksi']);
$totalPanjar  = formatcurrency($r['jumlah'],'IDR');

		// $amar_putusan = htmlspecialchars_decode($r['amar_putusan']);
		$pesan = "*Info Perkara PA Tembilahan* - $r[uraian] Nomor $r[nomor_perkara] atas nama $r[nama] sejumlah Rp $totalPanjar telah diterima pada tanggal $tanggal_transaksi. Dikirim otomatis oleh PA. Tembilahan";


		//echo "select *  from notif_perkara_pa.notif_pendaftaran_perkara where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$a'";echo "<br/>";
		$cek=mysql_query("select *  from notif_perkara_pa.notif_biaya_panjar where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and nomor_hp='$r[telepon]' and tanggal_transaksi='$r[tanggal_transaksi]' and notif_pesan='$pesan'");
		$jum=mysql_num_rows($cek);
		if($jum<1){
			
		$insert=mysql_query("insert into notif_perkara_pa.notif_biaya_panjar (perkara_id, nomor_perkara,tanggal_transaksi,notif_pesan,nomor_hp, date_input) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_transaksi]','$pesan','$r[telepon]','".date('Y-m-d H:i:s')."')");

		$insert_ok=mysql_query("select * from notif_perkara_pa.notif_biaya_panjar where perkara_id='$r[perkara_id]' and nomor_perkara = '$r[nomor_perkara]' and tanggal_transaksi = '$r[tanggal_transaksi]' and notif_pesan ='$pesan' and nomor_hp='$r[telepon]'");
		$id=mysql_fetch_array($insert_ok);	
		$insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$r[telepon]','$pesan','t',null,null,'$id[id_notif_biaya]')");	
		//exit;	
		}
		
  
  }		
  
  mysql_close();
		
			?>