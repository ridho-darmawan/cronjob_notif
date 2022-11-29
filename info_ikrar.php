
<?php
mysql_connect("localhost","root","") or die("koneksi server tidak bisa"); //server
include 'function_app.php';	

// input data ikrar dari sipp
$monitoring_ct =mysql_query("SELECT  a.`perkara_id`,a.`nomor_perkara`,a.`jenis_perkara_nama`,a.`proses_terakhir_id`,a.`proses_terakhir_text`,a.`pihak1_text`,
    b.`ikrar_talak`,b.`tanggal_sidang`,b.agenda,b.`keterangan` ,
    c.`nomor_akta_cerai`,c.`tgl_akta_cerai`,
    d.`tanggal_penetapan_sidang_ikrar`,
    g.`telepon`,
    DATEDIFF(CURDATE(),d.`tanggal_penetapan_sidang_ikrar`) AS waktu,
    h.nama,
    i.`telepon` AS telp_pengacara
    FROM sipp.perkara a 
    LEFT JOIN sipp.`perkara_jadwal_sidang` b ON a.`perkara_id`=b.`perkara_id` AND b.urutan = (SELECT MAX(urutan) FROM sipp.`perkara_jadwal_sidang` bb WHERE bb.`perkara_id`=a.`perkara_id`)
    LEFT JOIN sipp.`perkara_akta_cerai` c ON a.`perkara_id`=c.`perkara_id`
    LEFT JOIN sipp.perkara_ikrar_talak d ON a.perkara_id = d.perkara_id
    LEFT JOIN sipp.perkara_penetapan e ON a.perkara_id = e.perkara_id
    LEFT JOIN sipp.perkara_pihak1 f ON a.perkara_id = f.perkara_id
    LEFT JOIN sipp.pihak g ON f.pihak_id = g.id
    LEFT JOIN sipp.perkara_pengacara h ON h.perkara_id = f.perkara_id
    LEFT JOIN sipp.pihak i ON i.id = h.pengacara_id
    WHERE a.`jenis_perkara_nama` = 'cerai talak' 
    AND a.proses_terakhir_id = '293'
    AND b.`ikrar_talak` = 'y'
    AND c.`nomor_akta_cerai` IS NULL
    AND d.tgl_ikrar_talak IS NULL
    AND b.`tanggal_sidang` < CURDATE()
    AND h.aktif = 'y'
    AND h.`urutan` = '1'");

    while($r=mysql_fetch_array($monitoring_ct)){

        $pesan = "*Info Perkara PA Tembilahan* - Perkara $r[nomor_perkara] untuk segera melaksanakan ikrar talak. dikirim otomatis oleh PA. Tembilahan";

       

        $waktu_phs = strtotime($r['tanggal_penetapan_sidang_ikrar']);
        $waktu_phs_bulan = date("Y-m-d", strtotime("+1 month", $waktu_phs));

        // echo $final;
        // $waktu_gugur = strtotime("+1 day",strtotime($hitung_gugur_hari));

        if(!empty($r['telepon']))
        {
            $cekData = mysql_query("select * from notif_perkara_pa.notif_ikrar where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and tanggal_phs='$r[tanggal_penetapan_sidang_ikrar]' and tanggal_notif='$waktu_phs_bulan'");

            $jum=mysql_num_rows($cekData);

            if($jum<1){
                  $insert=mysql_query("insert into notif_perkara_pa.notif_ikrar (perkara_id, nomor_perkara,tanggal_phs,notif_pesan,nomor_hp, date_input, tanggal_notif) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_penetapan_sidang_ikrar]','$pesan','$r[telepon]','".date('Y-m-d H:i:s')."','$waktu_phs_bulan')");
            }
        }

        if(!empty($r['telp_pengacara']))
        {
            $cekData = mysql_query("select * from notif_perkara_pa.notif_ikrar where perkara_id='$r[perkara_id]' and nomor_perkara='$r[nomor_perkara]' and tanggal_phs='$r[tanggal_penetapan_sidang_ikrar]' and tanggal_notif='$waktu_phs_bulan' and nomor_hp='$r[telp_pengacara]'");

             if($jum<1){
                  $insert=mysql_query("insert into notif_perkara_pa.notif_ikrar (perkara_id, nomor_perkara,tanggal_phs,notif_pesan,nomor_hp, date_input, tanggal_notif) values ('$r[perkara_id]','$r[nomor_perkara]','$r[tanggal_penetapan_sidang_ikrar]','$pesan','$r[telp_pengacara]','".date('Y-m-d H:i:s')."','$waktu_phs_bulan')");
            }
        }



    }

// ===================================================================================================================================
    // get data ikrar yg telah sampai waktu notif

    $dataIkrar = mysql_query("SELECT * FROM notif_perkara_pa.`notif_ikrar` a
                                LEFT JOIN sipp.perkara_ikrar_talak b ON a.perkara_id = b.perkara_id
                                WHERE b.tgl_ikrar_talak IS NULL
                                AND tanggal_notif = CURDATE()");
    // $dataIkrar = mysql_query("SELECT * FROM notif_perkara_pa.`notif_ikrar` a
    //                             LEFT JOIN sipp.perkara_ikrar_talak b ON a.perkara_id = b.perkara_id
    //                             WHERE b.tgl_ikrar_talak IS NULL
    //                             AND tanggal_notif = '2022-08-08'");

    while ($ikrar= mysql_fetch_array($dataIkrar))
    {

        //format pesan

        $pesanBerikutnya = "*Info Perkara PA Tembilahan* - Perkara $ikrar[nomor_perkara] untuk segera melaksanakan ikrar talak. dikirim otomatis oleh PA. Tembilahan";

        //  info perkara pa tembilahan -
        // Pengucapan ikrar talak perkara nomor 'nomor perkara' atas nama 'nama p' sebagai pemohon melawan termohon '' yang dijadwalkan tanggal 'tgl sidang terakhir' belum dilaksanakan. Diharapkan  agar melapor kepada pelayanan Perkara Pengadilan Agama Tembilahan untuk penetapan hari sidang selanjutnya, sebelum tanggal 'tanggal gugur - sebulan'. Informasi ini dikirim otomatis oleh PA. Tembilahan.

        //cek ke table notif wa data tersedia apa belum

        $cekNotifWa = mysql_query("select * from notif_perkara_pa.notif_wa where no_tujuan='$ikrar[nomor_hp]' and isi_pesan='$pesanBerikutnya' and id_notif='$ikrar[id_notif_ikrar]'");

            $jumNotifWa=mysql_num_rows($cekNotifWa);

            if($jumNotifWa<1)
            {
                //insert ke table notif wa
                $insertNotifWa = mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim,id_notif) values ('$ikrar[nomor_hp]','$ikrar[notif_pesan]','t',null,null,'$ikrar[id_notif_ikrar]')");
            }


        //cek batas notif

        $waktu_phs1 = strtotime($ikrar['tanggal_phs']);
        $batas_notif = date("Y-m-d", strtotime("+6 month", $waktu_phs1));

        // echo $batas_notif;

        //cek notif terakhir

        $notif_terakhir = strtotime($ikrar['tanggal_notif']);
        $notif_berikutnya = date("Y-m-d", strtotime("+1 month", $notif_terakhir));

        // echo $notif_berikutnya;
        
        //insert tanggal notif berikutnya setelah notif pertama selesai

        if ($notif_berikutnya < $batas_notif ) {

            //cek data tersedia apa belum

           $cekNotifBerikutnya = mysql_query("select * from notif_perkara_pa.notif_ikrar where perkara_id='$ikrar[perkara_id]' and nomor_perkara='$ikrar[nomor_perkara]' and tanggal_phs='$ikrar[tanggal_phs]' and tanggal_notif='$notif_berikutnya'");

            $jumNotifberikutnya=mysql_num_rows($cekNotifBerikutnya);

            if($jumNotifberikutnya<1)
            {
                $insert=mysql_query("insert into notif_perkara_pa.notif_ikrar (perkara_id, nomor_perkara,tanggal_phs,notif_pesan,nomor_hp, date_input, tanggal_notif) values ('$ikrar[perkara_id]','$ikrar[nomor_perkara]','$ikrar[tanggal_phs]','$pesanBerikutnya','$ikrar[nomor_hp]','".date('Y-m-d H:i:s')."','$notif_berikutnya')");
            }

        }
       
    }
    
mysql_close();
?>