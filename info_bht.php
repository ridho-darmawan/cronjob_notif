<?php

	mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
	//include 'function_app.php';	

?>

<?php
    $cek_bht=mysql_query("SELECT a.nomor_perkara,
                a.perkara_id,
            a.jenis_perkara_text,
            a.tahapan_terakhir_id,
            a.tahapan_terakhir_text,
            b.tanggal_putusan,
            b.tanggal_bht,
            b.status_putusan_id,
            c.nama_gelar AS nama_ketua_majelis,
            d.nama_gelar AS nama_pp, 
            g.nomor_akta_cerai,
            g.no_seri_akta_cerai,
            g.tgl_akta_cerai
            FROM sipp.perkara a, sipp.perkara_putusan b, sipp.hakim_pn c, sipp.panitera_pn d, sipp.perkara_panitera_pn e, sipp.perkara_hakim_pn f, sipp.perkara_akta_cerai g
            WHERE b.status_putusan_id = 62
            AND b.tanggal_putusan >= '2022-01-01'
            AND a.`nomor_perkara`LIKE '%pdt.g%'
            AND g.tgl_akta_cerai IS NULL
            AND b.tanggal_bht <= CURDATE() 
            AND a.tahapan_terakhir_id < 20
            AND a.perkara_id = b.perkara_id 
            AND a.perkara_id = g.perkara_id 
            AND a.jenis_perkara_id = '347'
            AND f.perkara_id = a.perkara_id 
            AND e.perkara_id = a.perkara_id 
            AND f.hakim_id = c.id 
            AND e.panitera_id = d.id 
            AND f.urutan = '1'
            GROUP BY b.perkara_id 
            ORDER BY b.tanggal_bht DESC");

        while( $data = mysql_fetch_array($cek_bht))
	{
                $tanggal_bht  = tgl_indo($data['tanggal_bht']);

                $cek_kontak_panitera = mysql_query("Select * from smsku.daftar_kontak where jabatan='panitera'");

		while($kontak_panitera = mysql_fetch_array($cek_kontak_panitera)){
                        $no_panitera = $kontak_panitera['nomorhp'];	
		}

                $pesan="Info SIPP - Sidang Perkara Nomor $data[nomor_perkara] Telah di *BHT* Pada Tanggal $tanggal_bht";

                
                
                // notifikasi kepada panitera

                $cek_data = mysql_query("select *  from notif_perkara_pa.notif_bht where perkara_id='$data[perkara_id]' and nomor_perkara='$data[nomor_perkara]' and nomor_hp='$no_panitera' and tanggal_bht = '$data[tanggal_bht]'");

		$hitung_data=mysql_num_rows($cek_data);

                if($hitung_data < 1)
		{
                        $insert=mysql_query("insert into notif_perkara_pa.notif_bht (perkara_id, nomor_perkara,tanggal_bht,notif_pesan,nomor_hp, date_input) values ('$data[perkara_id]','$data[nomor_perkara]','$data[tanggal_bht]','$pesan','$no_panitera','".date('Y-m-d H:i:s')."')");

                        if ($insert) {
                               $get_id_notif_bht = mysql_query("select *  from notif_perkara_pa.notif_bht where perkara_id='$data[perkara_id]' and nomor_perkara='$data[nomor_perkara]' and nomor_hp='$no_panitera' and tanggal_bht = '$data[tanggal_bht]' and notif_pesan='$pesan'");

                               $id_notif_bht = mysql_fetch_array($get_id_notif_bht);	

                               $insert_to_notif_wa =mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$no_panitera','$pesan','t',null,null,'$id_notif_bht[id_notif_bht]')");	
                        }
                        
                       

                }

                //notifikasi 


                

                
        }

         mysql_close();
?>