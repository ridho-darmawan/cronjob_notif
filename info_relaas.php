<?php

	mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
	//include 'function_app.php';	


    $cek_relaas_h2 = mysql_query("SELECT a.id,a.perkara_id, 
    a.tanggal_sidang , 
    c.jurusita_id,
    c.jurusita_text,
    c.panitera_pengganti_text,
    c.panitera_pengganti_id,
    c.majelis_hakim_id,
    d.nomor_perkara
    FROM sipp.perkara_jadwal_sidang a 
    LEFT JOIN sipp.perkara_pelaksanaan_relaas b ON a.perkara_id = b.perkara_id 
    LEFT JOIN sipp.perkara d ON a.perkara_id = d.perkara_id
    LEFT JOIN sipp.perkara_penetapan c ON a.perkara_id = c.perkara_id 
    WHERE tanggal_sidang = CURDATE() + interval 2 day
    AND NOT EXISTS (SELECT * FROM sipp.perkara_pelaksanaan_relaas pr WHERE a.id = pr.sidang_id)
    GROUP BY a.id");

    if($cek_relaas_h2 === FALSE) { 
        trigger_error(mysql_error(), E_USER_ERROR);
    }

    if(!empty($cek_relaas_h2)){
        while($relaas = mysql_fetch_array($cek_relaas_h2))
        {
            $tanggal_sidang = tgl_indo($relaas['tanggal_sidang']);

            // $cek_kontak_panitera = mysql_query("Select * from smsku.daftar_kontak where jabatan='panitera'");

            // while($kontak_panitera = mysql_fetch_array($cek_kontak_panitera)){
            //     $no_panitera = $kontak_panitera['nomorhp'];	
            // }

            $pesan="Info SIPP - Relaas Perkara Nomor $relaas[nomor_perkara] *Belum Diupload*. Tanggal sidang $tanggal_sidang , $relaas[jurusita_text] ";

            //send notif relaas to Panitera
            //cek data di db notif untuk panitera

            // $get_data_panitera = mysql_query("select *  from notif_perkara_pa.notif_relaas where perkara_id='$relaas[perkara_id]' and nomor_perkara='$relaas[nomor_perkara]' and nomor_hp='$no_panitera' and tanggal_sidang = '$relaas[tanggal_sidang]'");

            // $cek_data_panitera = mysql_num_rows($get_data_panitera);

            // if($cek_data_panitera < 1)
            // {
            //     $insert=mysql_query("insert into notif_perkara_pa.notif_relaas (perkara_id, nomor_perkara,tanggal_sidang,notif_pesan,nomor_hp, date_input) values ('$relaas[perkara_id]','$relaas[nomor_perkara]','$relaas[tanggal_sidang]','$pesan','$no_panitera','".date('Y-m-d H:i:s')."')");

            //     if ($insert) {
            //         $get_id_notif_relaas = mysql_query("select *  from notif_perkara_pa.notif_relaas where perkara_id='$relaas[perkara_id]' and nomor_perkara='$relaas[nomor_perkara]' and nomor_hp='$no_panitera' and tanggal_sidang = '$relaas[tanggal_sidang]' and notif_pesan='$pesan'");

            //         $id_notif_relaas = mysql_fetch_array($get_id_notif_relaas);	

            //         $insert_to_notif_wa =mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$no_panitera','$pesan','t',null,null,'$id_notif_relaas[id_notif_relaas]')");	
            //     }
            // }


            // notif for jurusita

            $cek_kontak_jurusita=mysql_query("Select * from smsku.daftar_kontak where jabatan='jurusita'");

            while($kontak_jurusita=mysql_fetch_array($cek_kontak_jurusita)){
                $no_telpon_js[$kontak_jurusita['idsipp']]=$kontak_jurusita['nomorhp'];	
            }

            $nomor_telpon_js=$no_telpon_js[$relaas['jurusita_id']];

            $get_data_jurusita = mysql_query("select *  from notif_perkara_pa.notif_relaas where perkara_id='$relaas[perkara_id]' and nomor_perkara='$relaas[nomor_perkara]' and nomor_hp='$nomor_telpon_js' and tanggal_sidang = '$relaas[tanggal_sidang]'");

            $cek_data_jurusita = mysql_num_rows($get_data_jurusita);

            if($cek_data_jurusita < 1)
            {
                $insert=mysql_query("insert into notif_perkara_pa.notif_relaas (perkara_id, nomor_perkara,tanggal_sidang,notif_pesan,nomor_hp, date_input) values ('$relaas[perkara_id]','$relaas[nomor_perkara]','$relaas[tanggal_sidang]','$pesan','$nomor_telpon_js','".date('Y-m-d H:i:s')."')");

                if ($insert) {
                    $get_id_notif_relaas = mysql_query("select *  from notif_perkara_pa.notif_relaas where perkara_id='$relaas[perkara_id]' and nomor_perkara='$relaas[nomor_perkara]' and nomor_hp='$nomor_telpon_js' and tanggal_sidang = '$relaas[tanggal_sidang]' and notif_pesan='$pesan'");

                    $id_notif_relaas = mysql_fetch_array($get_id_notif_relaas);	

                    $insert_to_notif_wa =mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$nomor_telpon_js','$pesan','t',null,null,'$id_notif_relaas[id_notif_relaas]')");	
                }
            }

            // notif for panitera pengganti

            // $cek_kontak_pp=mysql_query("Select * from smsku.daftar_kontak where jabatan='pp'");

            // while($kontak_pp=mysql_fetch_array($cek_kontak_pp)){
            //     $no_telpon_pp[$kontak_pp['idsipp']]=$kontak_pp['nomorhp'];	
            // }

            // $nomor_telpon_pp=$no_telpon_pp[$relaas['panitera_pengganti_id']];

            // $get_data_pp = mysql_query("select *  from notif_perkara_pa.notif_relaas where perkara_id='$relaas[perkara_id]' and nomor_perkara='$relaas[nomor_perkara]' and nomor_hp='$nomor_telpon_pp' and tanggal_sidang = '$relaas[tanggal_sidang]'");

            // $cek_data_pp = mysql_num_rows($get_data_pp);

            // if($cek_data_pp < 1)
            // {
            //     $insert=mysql_query("insert into notif_perkara_pa.notif_relaas (perkara_id, nomor_perkara,tanggal_sidang,notif_pesan,nomor_hp, date_input) values ('$relaas[perkara_id]','$relaas[nomor_perkara]','$relaas[tanggal_sidang]','$pesan','$nomor_telpon_pp','".date('Y-m-d H:i:s')."')");

            //     if ($insert) {
            //         $get_id_notif_relaas = mysql_query("select *  from notif_perkara_pa.notif_relaas where perkara_id='$relaas[perkara_id]' and nomor_perkara='$relaas[nomor_perkara]' and nomor_hp='$nomor_telpon_pp' and tanggal_sidang = '$relaas[tanggal_sidang]' and notif_pesan='$pesan'");

            //         $id_notif_relaas = mysql_fetch_array($get_id_notif_relaas);	

            //         $insert_to_notif_wa =mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$nomor_telpon_pp','$pesan','t',null,null,'$id_notif_relaas[id_notif_relaas]')");	
            //     }
            // }

            //notif for ketua majelis

            // $cek_kontak_km=mysql_query("Select * from smsku.daftar_kontak where jabatan='wakil' or jabatan='hakim'");

            // while($kontak_km=mysql_fetch_array($cek_kontak_km)){
            //     $no_telpon_km[$kontak_km['idsipp']]=$kontak_km['nomorhp'];	
            // }

            // $km=explode(",",$relaas['majelis_hakim_id']);

            // $nomor_telpon_km=$no_telpon_km[$km[0]];

            // echo $nomor_telpon_km;

            // $get_data_km = mysql_query("select *  from notif_perkara_pa.notif_relaas where perkara_id='$relaas[perkara_id]' and nomor_perkara='$relaas[nomor_perkara]' and nomor_hp='$nomor_telpon_km' and tanggal_sidang = '$relaas[tanggal_sidang]'");

            // $cek_data_km = mysql_num_rows($get_data_km);

            // if($cek_data_km < 1)
            // {
            //     $insert=mysql_query("insert into notif_perkara_pa.notif_relaas (perkara_id, nomor_perkara,tanggal_sidang,notif_pesan,nomor_hp, date_input) values ('$relaas[perkara_id]','$relaas[nomor_perkara]','$relaas[tanggal_sidang]','$pesan','$nomor_telpon_km','".date('Y-m-d H:i:s')."')");

            //     if ($insert) {
            //         $get_id_notif_relaas = mysql_query("select *  from notif_perkara_pa.notif_relaas where perkara_id='$relaas[perkara_id]' and nomor_perkara='$relaas[nomor_perkara]' and nomor_hp='$nomor_telpon_km' and tanggal_sidang = '$relaas[tanggal_sidang]' and notif_pesan='$pesan'");

            //         $id_notif_relaas = mysql_fetch_array($get_id_notif_relaas);	

            //         $insert_to_notif_wa =mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$nomor_telpon_km','$pesan','t',null,null,'$id_notif_relaas[id_notif_relaas]')");	
            //     }
            // }





        }
        mysql_close();
    }
    
   
?>