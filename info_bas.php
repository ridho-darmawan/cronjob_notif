<?php

	mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
	//include 'function_app.php';	


    $cek_bas = mysql_query("SELECT sipp.perkara.perkara_id,
        sipp.perkara.alur_perkara_id,
        sipp.perkara.nomor_perkara,
        sipp.perkara.pihak1_text,
        sipp.perkara.pihak2_text,
        sipp.perkara.proses_terakhir_text,
        sipp.perkara_jadwal_sidang.tanggal_sidang,
        (GROUP_CONCAT(DISTINCT DATE_FORMAT(sipp.perkara_jadwal_sidang.tanggal_sidang, '%d-%m-%Y') ,' | ', sipp.perkara_jadwal_sidang.agenda ORDER BY sipp.perkara_jadwal_sidang.tanggal_sidang ASC SEPARATOR '<br>')) AS tanggal_sidang, 
        sipp.hakim_pn.nama_gelar AS ketua_majelis,
        sipp.hakim_pn.id AS ketua_majelis_id,
        sipp.panitera_pn.nama_gelar AS panitera_pengganti,
        sipp.panitera_pn.id AS panitera_pengganti_id
        FROM  sipp.perkara, sipp.perkara_jadwal_sidang, sipp.hakim_pn, sipp.panitera_pn, sipp.perkara_panitera_pn, sipp.perkara_hakim_pn
        WHERE perkara_jadwal_sidang.perkara_id = perkara.perkara_id 
        AND perkara_panitera_pn.perkara_id = perkara_jadwal_sidang.perkara_id
        AND perkara_panitera_pn.panitera_id = panitera_pn.id
        AND perkara_hakim_pn.perkara_id = perkara_jadwal_sidang.perkara_id
        AND perkara_hakim_pn.hakim_id = hakim_pn.id
        AND perkara_hakim_pn.urutan = 1
        AND (perkara_jadwal_sidang.edoc_bas IS NULL OR perkara_jadwal_sidang.edoc_bas ='' OR perkara_jadwal_sidang.edoc_bas = '0')
        -- AND YEAR (perkara_jadwal_sidang.tanggal_sidang) >= CURDATE()
        AND perkara_jadwal_sidang.tanggal_sidang = CURDATE()
        GROUP BY perkara_jadwal_sidang.perkara_id
        ORDER BY panitera_pn.nama_gelar DESC");


    if($cek_bas === FALSE) { 
        trigger_error(mysql_error(), E_USER_ERROR);
    }

    if(!empty($cek_bas)){
        while($bas = mysql_fetch_array($cek_bas))
        {
          
            $tanggal_sidang = tgl_indo($bas['tanggal_sidang']);

            $pesan=" Info SIPP - Perkara Nomor $bas[nomor_perkara] *Belum Upload BAS*. tanggal sidang $bas[tanggal_sidang] , Panitera Pengganti : $bas[panitera_pengganti] ";

           // notif for panitera pengganti

            $cek_kontak_pp=mysql_query("Select * from smsku.daftar_kontak where jabatan='pp'");

            while($kontak_pp=mysql_fetch_array($cek_kontak_pp)){
                $no_telpon_pp[$kontak_pp['idsipp']]=$kontak_pp['nomorhp'];	
            }

            $nomor_telpon_pp=$no_telpon_pp[$bas['panitera_pengganti_id']];

            $get_data_pp = mysql_query("select *  from notif_perkara_pa.notif_bas where perkara_id='$bas[perkara_id]' and nomor_perkara='$bas[nomor_perkara]' and nomor_hp='$nomor_telpon_pp' and tanggal_sidang = '$bas[tanggal_sidang]'");

            $cek_data_pp = mysql_num_rows($get_data_pp);

            if($cek_data_pp < 1)
            {
                $insert=mysql_query("insert into notif_perkara_pa.notif_bas (perkara_id, nomor_perkara,tanggal_sidang,notif_pesan,nomor_hp, date_input) values ('$bas[perkara_id]','$bas[nomor_perkara]','$bas[tanggal_sidang]','$pesan','$nomor_telpon_pp','".date('Y-m-d H:i:s')."')");

                if ($insert) {
                    $get_id_notif_bas = mysql_query("select *  from notif_perkara_pa.notif_bas where perkara_id='$bas[perkara_id]' and nomor_perkara='$bas[nomor_perkara]' and nomor_hp='$nomor_telpon_pp' and tanggal_sidang = '$bas[tanggal_sidang]' and notif_pesan='$pesan'");

                    $id_notif_bas = mysql_fetch_array($get_id_notif_bas);	

                    $insert_to_notif_wa =mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$nomor_telpon_pp','$pesan','t',null,null,'$id_notif_bas[id_notif_bas]')");	
                }
            }

            //notif for ketua majelis

            $cek_kontak_km=mysql_query("Select * from smsku.daftar_kontak where jabatan='wakil' or jabatan='hakim'");

            while($kontak_km=mysql_fetch_array($cek_kontak_km)){
                $no_telpon_km[$kontak_km['idsipp']]=$kontak_km['nomorhp'];	
            }

            // $km=explode(",",$bas['ketua_majelis_id']);

            $nomor_telpon_km=$no_telpon_km[$bas['ketua_majelis_id']];

            $get_data_km = mysql_query("select *  from notif_perkara_pa.notif_bas where perkara_id='$bas[perkara_id]' and nomor_perkara='$bas[nomor_perkara]' and nomor_hp='$nomor_telpon_km' and tanggal_sidang = '$bas[tanggal_sidang]'");

            $cek_data_km = mysql_num_rows($get_data_km);

            if($cek_data_km < 1)
            {
                $insert=mysql_query("insert into notif_perkara_pa.notif_bas (perkara_id, nomor_perkara,tanggal_sidang,notif_pesan,nomor_hp, date_input) values ('$bas[perkara_id]','$bas[nomor_perkara]','$bas[tanggal_sidang]','$pesan','$nomor_telpon_km','".date('Y-m-d H:i:s')."')");

                if ($insert) {
                    $get_id_notif_bas = mysql_query("select *  from notif_perkara_pa.notif_bas where perkara_id='$bas[perkara_id]' and nomor_perkara='$bas[nomor_perkara]' and nomor_hp='$nomor_telpon_km' and tanggal_sidang = '$bas[tanggal_sidang]' and notif_pesan='$pesan'");

                    $id_notif_bas = mysql_fetch_array($get_id_notif_bas);	

                    $insert_to_notif_wa =mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$nomor_telpon_km','$pesan','t',null,null,'$id_notif_bas[id_notif_bas]')");	
                }
            }





        }
        mysql_close();
    }
    
   
?>