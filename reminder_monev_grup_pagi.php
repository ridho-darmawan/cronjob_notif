<?php
	mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
    //include 'function_app.php';

    $cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='ketua' or jabatan='wakil'");

    while($kontak=mysql_fetch_array($cek_kontak))
    {
        $no_telpon_pimpinan[]=$kontak['nomorhp'];	
    }

    // =====================  CEK DATA BELUM TUNDA SIDANG ============================
    
    $cek_tunda_sidang=mysql_query("SELECT sipp.perkara.perkara_id,
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
                                    AND sipp.perkara_jadwal_sidang.tanggal_sidang = CURDATE() - INTERVAL 1 DAY
                                    AND (sipp.perkara.proses_terakhir_text = 'persidangan' OR sipp.perkara.proses_terakhir_text LIKE '%pertama%'OR sipp.perkara.proses_terakhir_text LIKE '%penetapan hari sidang ikrar talak%') 
                                    GROUP BY sipp.perkara_jadwal_sidang.perkara_id
                                    ORDER BY sipp.hakim_pn.nama_gelar DESC");

    $jum_tunda_sidang=0;
    $noPerkaraTundaSidang='';
    $ketuaMajelis='';

    while($tunda_sidang=mysql_fetch_array($cek_tunda_sidang)){
    //$tanggal1  = tgl_indo($r['tanggal_pendaftaran']);
        $jum_tunda_sidang++;
        $noPerkaraTundaSidang=$noPerkaraTundaSidang.$tunda_sidang['nomor_perkara'].'| KM : ' . $tunda_sidang['ketua_majelis'] . ' PP : '.$tunda_sidang['panitera_pengganti'].'<br/>';

        // echo $noPerkaraTundaSidang;
        
    }
    
    $breaks_tunda_sidang = array("<br />","<br>","<br/>");  
    $data_tunda_sidang = str_ireplace($breaks_tunda_sidang, "\r\n", $noPerkaraTundaSidang);

    $pesan_tunda_sidang="*==> Belum Input Tunda Sidang = $jum_tunda_sidang |*\r\n$data_tunda_sidang ";

    // =====================  CEK DATA PERKARA ECOURT BELUM DAFTAR ============================

     $cek_perkara_ecourt=mysql_query("SELECT nomor_register,
                                        tanggal_pendaftaran,
                                        status_pendaftaran_text AS status_pendaftaran,
                                        jumlah_skum,
                                        batas_pembayaran,
                                        nomor_perkara
                                        FROM sipp.perkara_efiling
                                        WHERE nomor_perkara IS NULL
                                        AND status_pendaftaran_text = 'menunggu pendaftaran'");

    $jum_perkara_ecourt=0;
    $noRegister='';
    $tglPendaftaran='';

    while($perkara_ecourt=mysql_fetch_array($cek_perkara_ecourt)){
        $tanggal  = tgl_indo($perkara_ecourt['tanggal_pendaftaran']);
        $jum_perkara_ecourt++;
        $noRegister=$noRegister.$perkara_ecourt['nomor_register'].'| Tgl Daftar : ' . $tanggal .'<br/>';

        // echo $noPerkaraTundaSidang;
        
    }
    
    $breaks_perkara_ecourt = array("<br />","<br>","<br/>");  
    $data_perkara_ecourt = str_ireplace($breaks_perkara_ecourt, "\r\n", $noRegister);

    $pesan_perkara_ecourt="*==> Perkara Ecourt Belum Register = $jum_perkara_ecourt |*\r\n$data_perkara_ecourt ";

    // =====================  CEK DATA PERKARA BELUM INPUT RELAAS ============================

    $cek_relaas=mysql_query("SELECT a.id,a.perkara_id, 
                            a.tanggal_sidang , 
                            c.jurusita_id,
                            c.jurusita_text,
                            e.nama_gelar,
                            c.panitera_pengganti_text,
                            c.panitera_pengganti_id,
                            c.majelis_hakim_id,
                            d.nomor_perkara,
                            a.`agenda`,
                            a.`dihadiri_oleh`
                            FROM sipp.perkara_jadwal_sidang a 
                            LEFT JOIN sipp.perkara_pelaksanaan_relaas b ON a.perkara_id = b.perkara_id 
                            LEFT JOIN sipp.perkara d ON a.perkara_id = d.perkara_id
                            LEFT JOIN sipp.perkara_penetapan c ON a.perkara_id = c.perkara_id 
                            LEFT JOIN sipp.jurusita e ON c.jurusita_id = e.id 
                            WHERE tanggal_sidang = CURDATE() 
                            AND NOT EXISTS (SELECT * FROM sipp.perkara_pelaksanaan_relaas pr WHERE a.id = pr.sidang_id)
                            GROUP BY a.id");

    $jum_relaas=0;
    $noPerkara='';
    $js='';

    while($relaas=mysql_fetch_array($cek_relaas)){
        // $tanggal  = tgl_indo($relaas['tanggal_pendaftaran']);
        $jum_relaas++;
        $noPerkara=$noPerkara.$relaas['nomor_perkara'].'| JS/P : ' . $relaas['nama_gelar'] .'<br/>';

    }

    $breaks_relaas = array("<br />","<br>","<br/>");  
    $data_relaas = str_ireplace($breaks_relaas, "\r\n", $noPerkara);

    $pesan_relaas="*==> Relaas Belum Upload = $jum_relaas |*\r\n$data_relaas ";

    // =====================  CEK DATA PERKARA BELUM PMH IKRAR TALAK============================

    $cek_pmh_ikrar_talak=mysql_query("SELECT a.nomor_perkara,
                                    a.pihak1_text,
                                    a.jenis_perkara_text,
                                    b.tanggal_putusan,
                                    b.tanggal_bht,
                                    b.status_putusan_id,
                                    c.nama_gelar AS nama_ketua_majelis, 
                                    c.keterangan AS phone_ketua_majelis, 
                                    d.nama_gelar AS nama_pp, 
                                    d.keterangan AS phone_pp
                                    FROM sipp.perkara a, sipp.perkara_putusan b, sipp.hakim_pn c, sipp.panitera_pn d, sipp.perkara_panitera_pn e, sipp.perkara_hakim_pn f
                                    WHERE b.status_putusan_id = 62
                                    AND b.tanggal_bht <= CURDATE() 
                                    AND a.perkara_id NOT IN (SELECT g.perkara_id FROM sipp.perkara_ikrar_talak g WHERE g.perkara_id = a.perkara_id GROUP BY g.perkara_id)
                                    AND a.perkara_id = b.perkara_id 
                                    AND a.jenis_perkara_id = '346'
                                    AND f.perkara_id = a.perkara_id 
                                    AND e.perkara_id = a.perkara_id 
                                    AND f.hakim_id = c.id 
                                    AND e.panitera_id = d.id 
                                    AND f.urutan = 1
                                    AND nomor_perkara NOT LIKE '%2015%'
                                    AND nomor_perkara NOT LIKE '%2016%'
                                    AND nomor_perkara NOT LIKE '%2017%'
                                    AND nomor_perkara NOT LIKE '%2018%'");

    $jum_pmh_ikrar_talak=0;
    $noPerkaraIkrarTalak='';

    if($cek_pmh_ikrar_talak === FALSE) { 
    trigger_error(mysql_error(), E_USER_ERROR);
}
    while($pmh_ikrar_talak=mysql_fetch_array($cek_pmh_ikrar_talak)){

        // echo $pmh_ikrar_talak;
        $tanggal  = tgl_indo($relaas['tanggal_pendaftaran']);
        $jum_pmh_ikrar_talak++;
        $noPerkaraIkrarTalak=$noPerkaraIkrarTalak.$pmh_ikrar_talak['nomor_perkara'].'| ' . $pmh_ikrar_talak['nama_ketua_majelis'] . ' | PP : '. $pmh_ikrar_talak['nama_pp'] .'<br/>';
       

    }

    $breaks_pmh_ikrar_talak = array("<br />","<br>","<br/>");  
    $data_pmh_ikrar_talak = str_ireplace($breaks_pmh_ikrar_talak, "\r\n", $noPerkaraIkrarTalak);

    $pesan_pmh_ikrar_talak="*==> Belum PMH Ikrar Talak = $jum_pmh_ikrar_talak |*\r\n$data_pmh_ikrar_talak ";


     // =====================  CEK DATA PERKARA BELUM BHT SEJAK PUTUS============================

    $cek_bht=mysql_query("SELECT perkara.perkara_id, 
                            perkara.alur_perkara_id, 
                            perkara.nomor_perkara, 
                            perkara.pihak1_text, 
                            perkara.pihak2_text, 
                            perkara.para_pihak,
                            perkara.tahapan_terakhir_text,
                            perkara.proses_terakhir_text,
                            (CASE WHEN perkara_putusan.tanggal_putusan IS NULL THEN '' ELSE (DATE_FORMAT(perkara_putusan.tanggal_putusan, '%d-%m-%Y')) END) AS putusan, 
                            (CASE WHEN perkara_putusan.tanggal_minutasi IS NULL THEN 'Belum Minutasi' ELSE (DATE_FORMAT(perkara_putusan.tanggal_minutasi, '%d-%m-%Y')) END) AS minutasi, 
                            (CASE WHEN perkara_putusan.tanggal_bht IS NULL THEN 'Belum BHT' ELSE (DATE_FORMAT(perkara_putusan.tanggal_bht, '%d-%m-%Y')) END) AS bht,
                            (CASE WHEN (SELECT TIMESTAMPDIFF(DAY, perkara_putusan.tanggal_putusan,CURRENT_DATE)) <= '14' THEN 'Dalam 14 Hari' ELSE '<a>Lebih 14 Hari</a>' END) AS waktu,
                            DATEDIFF(CURDATE(),perkara_putusan.tanggal_putusan) AS lama_proses_dari_putus,
                            hakim_pn.nama_gelar AS nama_ketua_majelis, 
                            hakim_pn.keterangan AS hp_ketua_majelis, 
                            panitera_pn.nama_gelar AS nama_pp, 
                            jurusita.nama_gelar AS nama_js
                            FROM sipp.perkara, sipp.perkara_putusan, sipp.hakim_pn, sipp.perkara_hakim_pn, sipp.panitera_pn, sipp.perkara_panitera_pn, sipp.jurusita, sipp.perkara_jurusita
                            WHERE tanggal_putusan IS NOT NULL
                            AND perkara_hakim_pn.urutan = 1
                            AND perkara.perkara_id = perkara_putusan.perkara_id 
                            AND perkara_panitera_pn.perkara_id = perkara_putusan.perkara_id 
                            AND perkara_jurusita.perkara_id = perkara_putusan.perkara_id 
                            AND perkara_hakim_pn.perkara_id = perkara_putusan.perkara_id 
                            AND perkara_hakim_pn.hakim_id = hakim_pn.id 
                            AND perkara_panitera_pn.panitera_id = panitera_pn.id 
                            AND perkara_jurusita.jurusita_id = jurusita.id
                            AND DATEDIFF(CURDATE(),perkara_putusan.tanggal_putusan) >= 0
                            AND perkara_putusan.tanggal_minutasi IS NOT NULL
                            AND perkara_putusan.tanggal_bht IS NULL
                            AND perkara_hakim_pn.aktif = 'Y'
                            AND perkara_panitera_pn.aktif = 'Y'
                            AND perkara_jurusita.aktif = 'Y'
                            AND nomor_perkara NOT LIKE '%2014%'
                            AND nomor_perkara NOT LIKE '%2015%'
                            AND nomor_perkara NOT LIKE '%2016%'
                            AND nomor_perkara NOT LIKE '%2017%'
                            AND nomor_perkara NOT LIKE '%2018%'
                            AND nomor_perkara NOT LIKE '%2019%'
                            AND nomor_perkara NOT LIKE '%2020%'
                            AND nomor_perkara NOT LIKE '%2021%'
                            AND nomor_perkara NOT LIKE '%Pdt.P%'
                            GROUP BY perkara_putusan.perkara_id 
                            ORDER BY jurusita.nama_gelar ASC, 
                            perkara_putusan.tanggal_putusan DESC,
                            perkara.perkara_id DESC, 
                            perkara_putusan.tanggal_bht DESC");

    $jum_bht=0;
    $noPerkaraBht='';

    while($bht=mysql_fetch_array($cek_bht)){
        // $tanggal  = tgl_indo($relaas['tanggal_pendaftaran']);
        // echo $bht;
        $jum_bht++;
         $noPerkaraBht=$noPerkaraBht.$bht['nomor_perkara'].'| JSP : '. $bht['nama_js'] .'| waktu : ' . $bht['lama_proses_dari_putus'] . ' Hari'.'<br/>';

    }

    $breaks_bht = array("<br />","<br>","<br/>");  
    $data_bht = str_ireplace($breaks_bht, "\r\n", $noPerkaraBht);

    $pesan_bht="*==> Belum BHT = $jum_bht |*\r\n$data_bht ";

   
    // =====================  CEK BELUM CETAK AKTA CERAI CG ============================

    $cek_cetak_ac_cg=mysql_query("SELECT a.nomor_perkara,
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
                                    AND e.aktif = 'Y'
                                    AND f.aktif = 'Y'
                                    AND b.tanggal_bht > '2021-01-01'
                                    GROUP BY b.perkara_id 
                                    ORDER BY d.nama_gelar ASC, b.tanggal_bht DESC");

    $jum_cetak_ac_cg=0;
    $noPerkaraCetakAcCg='';

    while($cetak_ac_cg=mysql_fetch_array($cek_cetak_ac_cg)){
        // $tanggal  = tgl_indo($relaas['tanggal_pendaftaran']);
        // echo $cetak_ac_cg;
        $jum_cetak_ac_cg++;
        $noPerkaraCetakAcCg=$noPerkaraCetakAcCg.$cetak_ac_cg['nomor_perkara'] . '| PP : ' . $cetak_ac_cg['nama_pp'] .'<br/>';
       

    }

    $breaks_cetak_ac_cg = array("<br />","<br>","<br/>");  
    $data_cetak_ac_cg = str_ireplace($breaks_cetak_ac_cg, "\r\n", $noPerkaraCetakAcCg);

    $pesan_cetak_ac_cg="*==> Belum Cetak Akta Cerai CG = $jum_cetak_ac_cg |*\r\n$data_cetak_ac_cg ";

    // =====================  CEK BELUM CETAK AKTA CERAI CT ============================

    $cek_cetak_ac_ct=mysql_query("SELECT a.nomor_perkara,
                                    a.jenis_perkara_text,
                                    b.tanggal_putusan,
                                    b.tanggal_bht,
                                    b.status_putusan_id,
                                    c.nama_gelar AS nama_ketua_majelis,
                                    d.nama_gelar AS nama_pp, 
                                    g.nomor_akta_cerai,
                                    g.no_seri_akta_cerai,
                                    g.tgl_akta_cerai,
                                    h.tgl_ikrar_talak,
                                    h.status_penetapan_ikrar_talak_id
                                    FROM sipp.perkara a, sipp.perkara_putusan b, sipp.hakim_pn c, sipp.panitera_pn d, sipp.perkara_panitera_pn e, sipp.perkara_hakim_pn f, sipp.perkara_akta_cerai g, sipp.perkara_ikrar_talak h
                                    WHERE g.tgl_akta_cerai IS NULL
                                    AND h.tgl_ikrar_talak IS NOT NULL
                                    AND h.status_penetapan_ikrar_talak_id = '1'
                                    AND h.tgl_ikrar_talak <= CURDATE() 
                                    AND a.perkara_id = b.perkara_id 
                                    AND a.perkara_id = g.perkara_id 
                                    AND a.perkara_id = h.perkara_id 
                                    AND a.jenis_perkara_id = '346'
                                    AND f.perkara_id = a.perkara_id 
                                    AND e.perkara_id = a.perkara_id 
                                    AND f.hakim_id = c.id 
                                    AND e.panitera_id = d.id 
                                    AND f.urutan = '1'
                                    AND a.nomor_perkara NOT LIKE '%2016%'
                                    AND a.nomor_perkara NOT LIKE '%2020%'
                                    GROUP BY h.perkara_id 
                                    ORDER BY h.tgl_ikrar_talak DESC");

    $jum_cetak_ac_ct=0;
    $noPerkaraCetakAcCt='';

    while($cetak_ac_ct=mysql_fetch_array($cek_cetak_ac_ct)){
        $jum_cetak_ac_ct++;
        $noPerkaraCetakAcCt=$noPerkaraCetakAcCt.$cetak_ac_ct['nomor_perkara']. '| PP : ' . $cetak_ac_ct['nama_pp'] .'<br/>';

    }

    $breaks_cetak_ac_ct = array("<br />","<br>","<br/>");  
    $data_cetak_ac_ct = str_ireplace($breaks_cetak_ac_ct, "\r\n", $noPerkaraCetakAcCt);

    $pesan_cetak_ac_ct="*==> Belum Cetak Akta Cerai CT = $jum_cetak_ac_ct |*\r\n$data_cetak_ac_ct ";

    // =====================  DATA BELUM PELAKSANAAN DELEGASI ============================

    $cek_delegasi=mysql_query("SELECT *, YEAR(A.tgl_delegasi) AS tahun				
				FROM sipp.delegasi_masuk AS A
				LEFT JOIN sipp.delegasi_proses_masuk AS B ON B.delegasi_id=A.id AND B.id_pn_asal=A.id_pn_asal AND B.perkara_id=A.perkara_id
				WHERE YEAR(A.tgl_delegasi)>=YEAR(NOW())
				AND A.status_kirim = '0'
				ORDER BY YEAR(A.tgl_delegasi) DESC");

     $jum_delegasi=0;
    $jspDelegasi='';
    $nama_jurusita='';

    while($delegasi=mysql_fetch_array($cek_delegasi)){
        $jum_delegasi++;
        // $totalDelegasi  = formatcurrency($delegasi['sisa_biaya'],'IDR');
         $jspDelegasi= $jspDelegasi.$delegasi['nomor_perkara'] . '| JS/P :' .$nama_jurusita.$delegasi['jurusita_nama']. '<br/>';
        //  $jspDelegasi=$jspDelegasi.$delegasi['jurusita_nama']. ' | No. Perkara : ' . $delegasi['nomor_perkara'] .'<br/>';
        // $jsp=$delegasi['jurusita_nama'] ;

    }

    $breaks_delegasi = array("<br />","<br>","<br/>");  
    $data_delegasi = str_ireplace($breaks_delegasi, "\r\n", $jspDelegasi);

    $pesan_delegasi="*==> Belum Pelaksanaan Delegasi = $jum_delegasi  |*\r\n$data_delegasi";

    // echo $pesan_delegasi;



    // ===================== kirim data ke database untuk dikirim ======================

    $notif_pesan = '*=== MONEV HARIAN SIPP PA TEMBILAHAN ===*'."\r\n\r\n".'A. PROSES PERKARA'."\r\n\r\n".$pesan_perkara_ecourt."\r\n".$pesan_tunda_sidang."\r\n".$pesan_relaas."\r\n".$pesan_delegasi."\r\n".$pesan_pmh_ikrar_talak."\r\n".'B. PUTUSAN PERKARA'."\r\n\r\n".$pesan_bht."\r\n".$pesan_cetak_ac_cg."\r\n".$pesan_cetak_ac_ct."\r\n\r\n".'Sinkron : '. date('d-m-Y H:i:s');

    // echo $notif_pesan;

    if($jum_tunda_sidang>0 || $jum_perkara_ecourt > 0 || $jum_relaas > 0 || $jum_pmh_ikrar_talak > 0  || $jum_bht > 0 || $jum_cetak_ac_cg > 0 || $jum_cetak_ac_ct > 0 ){
        // foreach($no_telpon_pimpinan as $a)
        // {
        $cek_pesan=mysql_query("select *  from notif_perkara_pa.notif_wa where no_tujuan IS NULL and isi_pesan='$notif_pesan'  and status_kirim='t'");
		$jum_pesan=mysql_num_rows($cek_pesan);
		if($jum_pesan < 1){
            $insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim) values (null,'$notif_pesan','t',null,null)");
        }
            	
        // }
    }

?>