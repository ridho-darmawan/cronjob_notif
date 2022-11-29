<?php
	mysql_connect("localhost","root","4dminpatbh") or die("koneksi server tidak bisa"); //server
   // include 'function_app.php';

    $cek_kontak=mysql_query("Select * from smsku.daftar_kontak where jabatan='ketua' or jabatan='wakil'");

    while($kontak=mysql_fetch_array($cek_kontak))
    {
        $no_telpon_pimpinan[]=$kontak['nomorhp'];	
    }

    // =====================  CEK DATA BELUM PMH ============================

    $cek_pmh=mysql_query("SELECT a.perkara_id,
                                    a.tanggal_pendaftaran,
                                    a.pihak1_text,
                                    a.pihak2_text,
                                    a.nomor_perkara,
                                    a.jenis_perkara_text,
                                    a.proses_terakhir_text,
                                    DATEDIFF(CURRENT_DATE, a.tanggal_pendaftaran) AS lamaproses,
                                    b.penetapan_majelis_hakim,
                                    c.tanggal_putusan
                                    FROM sipp.perkara a
                                    LEFT JOIN sipp.perkara_penetapan b ON a.perkara_id = b.perkara_id
                                    LEFT JOIN sipp.perkara_putusan c ON a.perkara_id = c.perkara_id 	
                                    WHERE a.tanggal_pendaftaran IS NOT NULL
                                    AND a.tahapan_terakhir_id = 10
                                    AND b.penetapan_majelis_hakim IS NULL
                                    AND c.tanggal_putusan IS NULL");

    $jum=0;
    $noPerkaraPmh='';

    while($pmh=mysql_fetch_array($cek_pmh)){
    //$tanggal1  = tgl_indo($r['tanggal_pendaftaran']);
        $jum++;
        $noPerkaraPmh=$noPerkaraPmh.$pmh['nomor_perkara'].'<br/>';
        
    }
    
    $breaks = array("<br />","<br>","<br/>");  
    $data_pmh = str_ireplace($breaks, "\r\n", $noPerkaraPmh);

    $pesan_pmh="*==> Belum PMH = $jum* |\r\n$data_pmh ";

    // =====================  CEK DATA BELUM PHS ============================

   $cek_phs=mysql_query("SELECT a.perkara_id,
                                            a.tanggal_pendaftaran,
                                            a.pihak1_text,
                                            a.pihak2_text,
                                            a.nomor_perkara,
                                            a.jenis_perkara_text,
                                            a.proses_terakhir_text,
                                            DATEDIFF(CURRENT_DATE, a.tanggal_pendaftaran) AS lamaproses,
                                            b.penetapan_majelis_hakim,
                                            b.majelis_hakim_nama,
                                            b.majelis_hakim_id,
                                            b.majelis_hakim_text,
                                            b.penetapan_panitera_pengganti,
                                            b.panitera_pengganti_text,
                                            b.penetapan_jurusita,
                                            b.jurusita_text,
                                            c.tanggal_putusan,
                                            d.hakim_nama
                                            FROM sipp.perkara a
                                            LEFT JOIN sipp.perkara_penetapan b ON a.perkara_id = b.perkara_id
                                            LEFT JOIN sipp.perkara_putusan c ON a.perkara_id = c.perkara_id 	
                                            LEFT JOIN sipp.perkara_hakim_pn d ON a.perkara_id = d.perkara_id 	
                                            WHERE a.tanggal_pendaftaran IS NOT NULL
                                            AND a.tahapan_terakhir_id > 10
                                            and d.urutan = 1
                                            AND b.penetapan_majelis_hakim IS NOT NULL
                                            AND b.penetapan_panitera_pengganti IS NOT NULL
                                            AND b.penetapan_jurusita IS NOT NULL
                                            AND b.penetapan_hari_sidang IS NULL 
                                            AND c.tanggal_putusan IS NULL");
    $jum_phs=0;
    $noPerkaraPhs='';

    while($phs=mysql_fetch_array($cek_phs)){
    //$tanggal1  = tgl_indo($r['tanggal_pendaftaran']);
        $jum_phs++;
        $noPerkaraPhs=$noPerkaraPhs.$phs['nomor_perkara'].' | KM : '. $phs['hakim_nama'] .'<br/>';    
    }
    
    $breaks_phs = array("<br />","<br>","<br/>");  
    $data_phs = str_ireplace($breaks_phs, "\r\n", $noPerkaraPhs);

    $pesan_phs="*==> Belum PHS = $jum_phs |*\r\n$data_phs ";

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
                                    AND sipp.perkara_jadwal_sidang.tanggal_sidang = CURDATE()
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
        //$noPerkaraIkrarTalak=$noPerkaraIkrarTalak.$pmh_ikrar_talak['nomor_perkara'].'| ' . $pmh_ikrar_talak['nama_ketua_majelis'] .'<br/>';
	$noPerkaraIkrarTalak=$noPerkaraIkrarTalak.$pmh_ikrar_talak['nomor_perkara'].'| ' . $pmh_ikrar_talak['nama_ketua_majelis'] . ' | PP : '. $pmh_ikrar_talak['nama_pp'] .'<br/>';

    }

    $breaks_pmh_ikrar_talak = array("<br />","<br>","<br/>");  
    $data_pmh_ikrar_talak = str_ireplace($breaks_pmh_ikrar_talak, "\r\n", $noPerkaraIkrarTalak);

    $pesan_pmh_ikrar_talak="*==> Belum PMH Ikrar Talak = $jum_pmh_ikrar_talak |*\r\n$data_pmh_ikrar_talak ";

    // =====================  CEK DATA PERKARA BELUM UPLOAD PETITUM============================

    $cek_petitum=mysql_query("SELECT a.tanggal_pendaftaran, 
                                a.para_pihak, 
                                a.nomor_perkara, 
                                a.proses_terakhir_text 
                                FROM sipp.perkara a
                                WHERE (a.petitum_dok = '' OR a.petitum_dok IS NULL) 
                                AND (a.alur_perkara_id='15' OR a.alur_perkara_id ='16')
                                AND nomor_perkara NOT LIKE '%2014%'
                                AND nomor_perkara NOT LIKE '%2015%' 
                                AND nomor_perkara NOT LIKE '%2016%' 
                                AND nomor_perkara NOT LIKE '%2017%' 
                                AND nomor_perkara NOT LIKE '%2018%'
                                AND nomor_perkara NOT LIKE '%2019%' 
                                AND nomor_perkara NOT LIKE '%2020%'  
                                AND nomor_perkara NOT LIKE '%2021%' 
                                ORDER BY a.tanggal_pendaftaran DESC");

    $jum_petitum=0;
    $noPerkaraPetitum='';

    while($petitum=mysql_fetch_array($cek_petitum)){
        // $tanggal  = tgl_indo($relaas['tanggal_pendaftaran']);
        $jum_petitum++;
        $noPerkaraPetitum=$noPerkaraPetitum.$petitum['nomor_perkara'].'| ' .'<br/>';

    }

    $breaks_petitum = array("<br />","<br>","<br/>");  
    $data_petitum = str_ireplace($breaks_petitum, "\r\n", $noPerkaraPetitum);

    $pesan_petitum="*==> Belum Upload Petitum = $jum_petitum |*\r\n$data_petitum ";

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

    
     // =====================  CEK BELUM UPLOAD EDOC AKTA CERAI============================

    $cek_ac=mysql_query("SELECT DISTINCT a.nomor_perkara,
                                        a.pihak1_text,
                                        a.jenis_perkara_text,
                                        b.tanggal_putusan,
                                        a.tahapan_terakhir_id,
                                        a.tahapan_terakhir_text,
                                        a.proses_terakhir_id,
                                        a.proses_terakhir_text,
                                        c.nama_gelar AS nama_ketua_majelis, 
                                        c.keterangan AS phone_ketua_majelis,
                                        d.nama_gelar AS nama_pp,
                                        g.tgl_akta_cerai,
                                        g.nomor_akta_cerai,
                                        g.no_seri_akta_cerai,
                                        g.akta_cerai_dok, 
                                        (CASE WHEN g.akta_cerai_dok = '' OR g.akta_cerai_dok IS NULL THEN 'belum diupload' ELSE 'telah diupload' END) AS status_dok
                                        FROM sipp.perkara a, sipp.perkara_putusan b, sipp.hakim_pn c, sipp.panitera_pn d, sipp.perkara_panitera_pn e, sipp.perkara_hakim_pn f, sipp.perkara_akta_cerai g
                                        WHERE (g.akta_cerai_dok IS NULL OR g.akta_cerai_dok = '')
                                        AND g.tgl_akta_cerai IS NOT NULL
                                        AND g.perkara_id = a.perkara_id
                                        AND a.perkara_id = b.perkara_id 
                                        AND a.tahapan_terakhir_id  >= 19
                                        AND a.proses_terakhir_id >= 296
                                        AND f.perkara_id = a.perkara_id 
                                        AND e.perkara_id = a.perkara_id 
                                        AND f.hakim_id = c.id 
                                        AND e.panitera_id = d.id 
                                        AND f.urutan = 1
                                        AND nomor_perkara NOT LIKE '%2014%'
                                        AND a.nomor_perkara NOT LIKE '%2015%'
                                        AND a.nomor_perkara NOT LIKE '%2016%'
                                        AND a.nomor_perkara NOT LIKE '%2017%'
                                        AND a.nomor_perkara NOT LIKE '%2018%'
                                        AND a.nomor_perkara NOT LIKE '%2019%'
                                        AND a.nomor_perkara NOT LIKE '%2020%'
                                        AND a.nomor_perkara NOT LIKE '%2021%'
                                        ORDER BY a.perkara_id DESC, g.nomor_akta_cerai DESC");

    $jum_ac=0;
    $noPerkaraAc='';

    while($ac=mysql_fetch_array($cek_ac)){
        // $tanggal  = tgl_indo($relaas['tanggal_pendaftaran']);
        // echo $ac;
        $jum_ac++;
        $noPerkaraAc=$noPerkaraAc.$ac['nomor_perkara'] .'<br/>';

    }

    $breaks_ac = array("<br />","<br>","<br/>");  
    $data_ac = str_ireplace($breaks_ac, "\r\n", $noPerkaraAc);

    $pesan_ac="*==> Belum Upload E-doc AC = $jum_ac |*\r\n$data_ac ";

     // =====================  CEK BELUM UPLOAD EDOC PUTUSAN ============================

    $cek_doc_putusan=mysql_query("SELECT perkara.perkara_id, 
                                    perkara.nomor_perkara, 
                                    perkara.jenis_perkara_text,
                                    perkara.proses_terakhir_text, 
                                    perkara.pihak1_text, 
                                    perkara.tanggal_pendaftaran, 
                                    perkara_putusan.tanggal_putusan,
                                    perkara_putusan.tanggal_bht,
                                    status_putusan_id, status_putusan.nama AS status_putusan_nama, 
                                    hakim_pn.nama_gelar AS nama_ketua_majelis,
                                    panitera_pn.nama_gelar AS nama_pp,
                                    perkara_putusan.diinput_oleh AS konseptor
                                    FROM sipp.perkara, sipp.perkara_putusan, sipp.hakim_pn, sipp.perkara_hakim_pn, sipp.panitera_pn, sipp.perkara_panitera_pn, sipp.status_putusan
                                    WHERE perkara.perkara_id = perkara_putusan.perkara_id
                                    AND perkara_hakim_pn.hakim_id = hakim_pn.id 
                                    AND perkara_hakim_pn.perkara_id = perkara_putusan.perkara_id 
                                    AND perkara_panitera_pn.panitera_id = panitera_pn.id 
                                    AND perkara_panitera_pn.perkara_id = perkara_putusan.perkara_id
                                    AND perkara_putusan.status_putusan_id = status_putusan.id
                                    AND tanggal_putusan IS NOT NULL
                                    AND perkara_putusan.amar_putusan_dok IS NULL
                                    AND tanggal_putusan NOT LIKE '%2014%'
                                    AND tanggal_putusan NOT LIKE '%2015%'
                                    AND tanggal_putusan NOT LIKE '%2016%'
                                    AND tanggal_putusan NOT LIKE '%2017%'
                                    AND tanggal_putusan NOT LIKE '%2018%'
                                    AND tanggal_putusan NOT LIKE '%2019%'
                                    GROUP BY perkara_putusan.perkara_id DESC
                                    ORDER BY perkara.perkara_id DESC");

    $jum_doc_putusan=0;
    $noPerkaraDocPutusan='';

    while($doc_putusan=mysql_fetch_array($cek_doc_putusan)){
        // $tanggal  = tgl_indo($relaas['tanggal_pendaftaran']);
        // echo $doc_putusan;
        $jum_doc_putusan++;
        $noPerkaraDocPutusan=$noPerkaraDocPutusan.$doc_putusan['nomor_perkara'].'| KM : ' . $doc_putusan['nama_ketua_majelis'] .'<br/>';
    }

    $breaks_doc_putusan = array("<br />","<br>","<br/>");  
    $data_doc_putusan = str_ireplace($breaks_doc_putusan, "\r\n", $noPerkaraDocPutusan);

    $pesan_doc_putusan="*==> Belum Upload E-doc Putusan = $jum_doc_putusan |*\r\n$data_doc_putusan ";

     // =====================  CEK BELUM INPUT AMAR PUTUSAN ============================

    $cek_amar_putusan=mysql_query("SELECT perkara.perkara_id, 
                                    perkara.alur_perkara_id, 
                                    perkara.nomor_perkara, 
                                    pihak1_text, 
                                    pihak2_text, 
                                    REPLACE(para_pihak,'<br />',' ') AS para_pihak,
                                    perkara_jadwal_sidang.agenda, 
                                    perkara_jadwal_sidang.tanggal_sidang, 
                                    (GROUP_CONCAT(DATE_FORMAT(perkara_jadwal_sidang.tanggal_sidang, '%d-%m-%Y') ,'&nbsp&nbsp;','<a>|</a>&nbsp&nbsp;', perkara_jadwal_sidang.agenda ORDER BY perkara_jadwal_sidang.urutan DESC SEPARATOR '<br>')) AS agenda,
                                    (CASE WHEN (SELECT TIMESTAMPDIFF(HOUR, perkara_jadwal_sidang.tanggal_sidang,CURRENT_DATE)) <= '24' THEN 'DALAM 24 JAM' ELSE '<a>LEBIH DARI 24 JAM</a>' END) AS waktu,
                                    hakim_pn.nama_gelar AS ketua_majelis, 
                                    hakim_pn.keterangan AS phone, 
                                    panitera_pn.nama_gelar AS panitera_pengganti,
                                    (SELECT GROUP_CONCAT(DISTINCT hakim_pn.nama_gelar ORDER BY perkara_hakim_pn.id ASC SEPARATOR '; ') AS nama_hakim FROM sipp.hakim_pn, sipp.perkara_hakim_pn WHERE  perkara_hakim_pn.hakim_id = hakim_pn.id AND perkara_hakim_pn.perkara_id = perkara_jadwal_sidang.perkara_id) AS majelis_hakim
                                    FROM sipp.perkara, sipp.perkara_jadwal_sidang, sipp.hakim_pn, sipp.perkara_hakim_pn, sipp.panitera_pn, sipp.perkara_panitera_pn  
                                    WHERE perkara_jadwal_sidang.perkara_id = perkara.perkara_id 
                                    AND perkara_panitera_pn.perkara_id = perkara_jadwal_sidang.perkara_id
                                    AND perkara_panitera_pn.panitera_id = panitera_pn.id 
                                    AND perkara_hakim_pn.perkara_id = perkara_jadwal_sidang.perkara_id
                                    AND perkara_hakim_pn.hakim_id = hakim_pn.id
                                    AND perkara_hakim_pn.urutan = 1 
                                    AND perkara_jadwal_sidang.agenda NOT LIKE '%sela%' 
                                    AND (perkara_jadwal_sidang.keterangan LIKE '%putus%' OR perkara_jadwal_sidang.keterangan LIKE '%penetapan%' OR perkara_jadwal_sidang.keterangan LIKE '%cabut%' OR perkara_jadwal_sidang.keterangan LIKE '%gugur%' OR perkara_jadwal_sidang.keterangan LIKE '%tolak%') 
                                    AND perkara_jadwal_sidang.ditunda='T' 
                                    AND (perkara.proses_terakhir_id = '81' OR perkara.proses_terakhir_id = '200')
                                    AND perkara_jadwal_sidang.tanggal_sidang <= CURDATE()
                                    AND perkara_hakim_pn.aktif = 'Y'
                                    AND perkara_panitera_pn.aktif = 'Y'
                                    GROUP BY perkara_jadwal_sidang.perkara_id 
                                    ORDER BY perkara_jadwal_sidang.perkara_id DESC");

    $jum_amar_putusan=0;
    $noPerkaraAmarPutusan='';

    while($amar_putusan=mysql_fetch_array($cek_amar_putusan)){
        // $tanggal  = tgl_indo($relaas['tanggal_pendaftaran']);
        // echo $amar_putusan;
        $jum_amar_putusan++;
        $noPerkaraAmarPutusan=$noPerkaraAmarPutusan.$amar_putusan['nomor_perkara'].'| KM : ' . $amar_putusan['ketua_majelis'] .'<br/>';

    }

    $breaks_amar_putusan = array("<br />","<br>","<br/>");  
    $data_amar_putusan = str_ireplace($breaks_amar_putusan, "\r\n", $noPerkaraAmarPutusan);

    $pesan_amar_putusan="*==> Belum Input Amar Putusan = $jum_amar_putusan |*\r\n$data_amar_putusan ";

    // =====================  CEK BELUM INPUT AMAR IKRAR ============================

    $cek_amar_ikrar=mysql_query("SELECT a.nomor_perkara,
                                    a.pihak1_text,
                                    a.jenis_perkara_text,
                                    b.tanggal_putusan,
                                    b.tanggal_bht,
                                    b.status_putusan_id,
                                    c.nama_gelar AS nama_ketua_majelis, 
                                    c.keterangan AS phone_ketua_majelis,
                                    d.nama_gelar AS nama_pp, 
                                    g.tanggal_penetapan_sidang_ikrar AS phs_ikrar,  
                                    g.tanggal_sidang_pertama,
                                    g.tgl_ikrar_talak, g.amar_ikrar_talak,
                                    (SELECT MAX(h.tanggal_sidang)
                                    FROM sipp.perkara_jadwal_sidang h
                                    WHERE h.perkara_id = a.perkara_id
                                    AND h.ditunda ='T'
                                    AND h.keterangan LIKE '%ikrar%'
                                    AND (h.keterangan NOT LIKE '%lapor%' OR h.keterangan NOT LIKE '%ditentukan kemudian%')
                                    ORDER BY h.tanggal_sidang DESC) AS tgl_sidang_last,
                                    (CASE WHEN (SELECT TIMESTAMPDIFF(HOUR,tgl_sidang_last,CURRENT_DATE)) <= '24' THEN 'DALAM 24 JAM' ELSE '<a>LEBIH DARI 24 JAM</a>' END) AS waktu
                                    FROM sipp.perkara a, sipp.perkara_putusan b, sipp.hakim_pn c, sipp.panitera_pn d, sipp.perkara_panitera_pn e, sipp.perkara_hakim_pn f, sipp.perkara_ikrar_talak g
                                    WHERE (SELECT MAX(h.tanggal_sidang) 
                                    FROM sipp.perkara_jadwal_sidang h
                                    WHERE h.perkara_id = a.perkara_id
                                    AND h.ditunda ='T'
                                    AND h.keterangan LIKE '%ikrar%'
                                    AND a.nomor_perkara NOT LIKE '%2017%'
                                    AND (h.keterangan NOT LIKE '%lapor%' OR h.keterangan NOT LIKE '%ditentukan kemudian%')
                                    ORDER BY h.tanggal_sidang DESC) <= CURDATE()
                                    AND g.amar_ikrar_talak IS NULL 
                                    AND b.status_putusan_id = 62
                                    AND g.perkara_id = a.perkara_id
                                    AND a.perkara_id = b.perkara_id 
                                    AND a.jenis_perkara_id = '346'
                                    AND f.perkara_id = a.perkara_id 
                                    AND e.perkara_id = a.perkara_id 
                                    AND f.hakim_id = c.id 
                                    AND e.panitera_id = d.id 
                                    AND f.urutan = 1");

    $jum_amar_ikrar=0;
    $noPerkaraAmarIkrar='';

    while($amar_ikrar=mysql_fetch_array($cek_amar_ikrar)){
        // $tanggal  = tgl_indo($relaas['tanggal_pendaftaran']);
        // echo $amar_ikrar;
        $jum_amar_ikrar++;
        $noPerkaraAmarIkrar=$noPerkaraAmarIkrar.$amar_ikrar['nomor_perkara'].'| KM : ' . $amar_ikrar['nama_ketua_majelis'] .' PP : '. $amar_ikrar['nama_pp'] .'<br/>';

    }

    $breaks_amar_ikrar = array("<br />","<br>","<br/>");  
    $data_amar_ikrar = str_ireplace($breaks_amar_ikrar, "\r\n", $noPerkaraAmarIkrar);

    $pesan_amar_ikrar="*==> Belum Input Amar Ikrar = $jum_amar_ikrar |*\r\n$data_amar_ikrar ";


     // =====================  CEK PERKARA BELUM MINUTASI ============================

    $cek_minutasi=mysql_query("SELECT perkara.perkara_id, 
                                    perkara.alur_perkara_id, 
                                    perkara.nomor_perkara, 
                                    perkara.pihak1_text, 
                                    perkara.pihak2_text, 
                                    perkara.para_pihak,
                                    perkara.tahapan_terakhir_text,
                                    perkara.proses_terakhir_text,
                                    (CASE WHEN perkara_putusan.tanggal_putusan IS NULL THEN '' ELSE (DATE_FORMAT(perkara_putusan.tanggal_putusan, '%d-%m-%Y')) END) AS putusan,
                                    (CASE WHEN perkara_putusan.tanggal_minutasi IS NULL THEN 'Belum Minutasi' ELSE (DATE_FORMAT(perkara_putusan.tanggal_minutasi, '%d-%m-%Y')) END) AS minutasi,
                                    (CASE WHEN (SELECT TIMESTAMPDIFF(DAY, perkara_putusan.tanggal_putusan,CURRENT_DATE)) <= '1' THEN 'Dalam 1 Hari' ELSE '<a>Lebih 1 Hari</a>' END) AS waktu,
                                    DATEDIFF(CURDATE(),perkara_putusan.tanggal_putusan) AS lama_proses_dari_putus,
                                    hakim_pn.nama_gelar AS nama_ketua_majelis, 
                                    hakim_pn.keterangan AS hp_ketua_majelis,
                                    panitera_pn.nama_gelar AS nama_pp, 
                                    jurusita.nama_gelar AS nama_js,
                                    perkara_putusan.diinput_oleh AS konseptor
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
                                    AND tanggal_minutasi IS NULL
                                    AND perkara_hakim_pn.aktif = 'Y'
                                    AND perkara_panitera_pn.aktif = 'Y'
                                    AND perkara_jurusita.aktif = 'Y'
                                    GROUP BY perkara_putusan.perkara_id 
                                    ORDER BY perkara_putusan.tanggal_putusan DESC, 
                                    perkara_putusan.tanggal_minutasi DESC");

    $jum_minutasi=0;
    $noPerkaraMinutasi='';

    while($minutasi=mysql_fetch_array($cek_minutasi)){
        // $tanggal  = tgl_indo($relaas['tanggal_pendaftaran']);
        // echo $minutasi;
        $jum_minutasi++;
        $noPerkaraMinutasi=$noPerkaraMinutasi.$minutasi['nomor_perkara'].'| KM : ' . $minutasi['nama_ketua_majelis']  .'<br/>';

    }

    $breaks_minutasi = array("<br />","<br>","<br/>");  
    $data_minutasi = str_ireplace($breaks_minutasi, "\r\n", $noPerkaraMinutasi);

    $pesan_minutasi="*==> Belum Minutasi = $jum_minutasi |*\r\n$data_minutasi ";

    // =====================  CEK PERKARA SUDAH PUTUS BELUM REDAKSI ============================

    $cek_redaksi_putus=mysql_query("SELECT
								  b.nomor_perkara,
								  c.hakim_nama,
								  d.panitera_nama,
								  a.tanggal_putusan 
								FROM
								  sipp.perkara_putusan AS a 
								  LEFT JOIN sipp.perkara AS b 
									ON a.perkara_id = b.perkara_id 
								  RIGHT JOIN sipp.perkara_hakim_pn AS c 
									ON a.perkara_id = c.perkara_id 
								  RIGHT JOIN sipp.perkara_panitera_pn AS d 
									ON a.perkara_id = d.perkara_id 
								WHERE a.perkara_id NOT IN 
								  (SELECT 
									e.perkara_id 
								  FROM
									sipp.perkara_biaya AS e 
								  WHERE e.jenis_biaya_id = '157') 
								  AND c.urutan = '1' 
								  AND c.aktif = 'Y' 
								  AND d.aktif = 'Y' 
								ORDER BY b.tanggal_pendaftaran ASC");

    $jum_redaksi_putus=0;
    $noPerkaraRedaksiPutus='';

    while($redaksi_putus=mysql_fetch_array($cek_redaksi_putus)){
        // $tanggal  = tgl_indo($relaas['tanggal_pendaftaran']);
        // echo $redaksi_putus;
        $jum_redaksi_putus++;
        $noPerkaraRedaksiPutus=$noPerkaraRedaksiPutus.$redaksi_putus['nomor_perkara']. ' | KM  : ' . $redaksi_putus['hakim_nama'] .'<br/>';
    }

    $breaks_redaksi_putus = array("<br />","<br>","<br/>");  
    $data_redaksi_putus = str_ireplace($breaks_redaksi_putus, "\r\n", $noPerkaraRedaksiPutus);

    $pesan_redaksi_putus="*==> Perkara Putus belum redaksi = $jum_redaksi_putus |*\r\n$data_redaksi_putus ";


    // =====================  CEK SUDAH REDAKSI BELUM PUTUS ============================

    $cek_redaksi_belum_putus=mysql_query("SELECT
								  a.perkara_id,
								  b.nomor_perkara,
								  c.hakim_nama,
								  d.panitera_nama,
								  e.tanggal_putusan,
								  a.tanggal_transaksi 
								FROM
								  sipp.perkara_biaya AS a 
								  LEFT JOIN sipp.perkara AS b 
									ON a.perkara_id = b.perkara_id 
								  RIGHT JOIN sipp.perkara_hakim_pn AS c 
									ON a.perkara_id = c.perkara_id 
								  RIGHT JOIN sipp.perkara_panitera_pn AS d 
									ON a.perkara_id = d.perkara_id 
								  LEFT JOIN sipp.perkara_putusan AS e 
									ON a.perkara_id = e.perkara_id 
								WHERE a.jenis_biaya_id = '157'
								  AND e.tanggal_putusan IS NULL 
								  AND c.urutan = '1' 
								  AND c.aktif = 'Y' 
								  AND d.aktif = 'Y' 
								ORDER BY a.tanggal_transaksi, c.hakim_nama ASC");

    $jum_redaksi_belum_putus=0;
    $noPerkaraRedaksiBelumPutus='';

    while($redaksi_belum_putus=mysql_fetch_array($cek_redaksi_belum_putus)){
        // $tanggal  = tgl_indo($relaas['tanggal_pendaftaran']);
        // echo $redaksi_belum_putus;
        $jum_redaksi_belum_putus++;
         $noPerkaraRedaksiBelumPutus=$noPerkaraRedaksiBelumPutus.$redaksi_belum_putus['nomor_perkara']. ' | KM  : ' . $redaksi_belum_putus['hakim_nama'] .'<br/>';
    }

    $breaks_redaksi_belum_putus = array("<br />","<br>","<br/>");  
    $data_redaksi_belum_putus = str_ireplace($breaks_redaksi_belum_putus, "\r\n", $noPerkaraRedaksiBelumPutus);

    $pesan_redaksi_belum_putus="*==> Perkara Sudah redaksi belum putus = $jum_redaksi_belum_putus |*\r\n$data_redaksi_belum_putus ";


    
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
        $noPerkaraCetakAcCt=$noPerkaraCetakAcCt.$cetak_ac_ct['nomor_perkara']. '| PP : ' . $cetak_ac_ct['nama_pp'] .'<br/>';    }

    $breaks_cetak_ac_ct = array("<br />","<br>","<br/>");  
    $data_cetak_ac_ct = str_ireplace($breaks_cetak_ac_ct, "\r\n", $noPerkaraCetakAcCt);

    $pesan_cetak_ac_ct="*==> Belum Cetak Akta Cerai CT = $jum_cetak_ac_ct |*\r\n$data_cetak_ac_ct ";

     // =====================  PERKARA BELUM UPLOAD ANONIMISASI ============================

    $cek_anonimisasi=mysql_query("SELECT perkara.perkara_id, 
                                    perkara.nomor_perkara, 
                                    perkara.jenis_perkara_text,
                                    perkara.proses_terakhir_text, 
                                    perkara.pihak1_text, 
                                    perkara.tanggal_pendaftaran, 
                                    perkara_putusan.tanggal_putusan,
                                    perkara_putusan.tanggal_bht,
                                    status_putusan_id, status_putusan.nama AS status_putusan_nama, 
                                    hakim_pn.nama_gelar AS nama_ketua_majelis,
                                    panitera_pn.nama_gelar AS nama_pp,
                                    perkara_putusan.diinput_oleh AS konseptor
                                    FROM sipp.perkara, sipp.perkara_putusan, sipp.hakim_pn, sipp.perkara_hakim_pn, sipp.panitera_pn, sipp.perkara_panitera_pn, sipp.status_putusan
                                    WHERE perkara.perkara_id = perkara_putusan.perkara_id
                                    AND perkara_hakim_pn.hakim_id = hakim_pn.id 
                                    AND perkara_hakim_pn.perkara_id = perkara_putusan.perkara_id 
                                    AND perkara_panitera_pn.panitera_id = panitera_pn.id 
                                    AND perkara_panitera_pn.perkara_id = perkara_putusan.perkara_id
                                    AND perkara_putusan.status_putusan_id = status_putusan.id
                                    AND tanggal_putusan IS NOT NULL
                                    AND perkara_putusan.amar_putusan_anonimisasi_dok IS NULL
                                    AND nomor_perkara NOT LIKE '%2014%'
                                    AND nomor_perkara NOT LIKE '%2015%'
                                    GROUP BY perkara_putusan.perkara_id 
                                    ORDER BY perkara_putusan.tanggal_putusan DESC,
                                    perkara.perkara_id DESC");

    $jum_anonimisasi=0;
    $noPerkaraAnonimisasi='';

    while($anonimisasi=mysql_fetch_array($cek_anonimisasi)){
        $jum_anonimisasi++;
        $noPerkaraAnonimisasi=$noPerkaraAnonimisasi.$anonimisasi['nomor_perkara']. ' | KM : '. $anonimisasi['nama_ketua_majelis'] .'<br/>';
    }

    $breaks_anonimisasi = array("<br />","<br>","<br/>");  
    $data_anonimisasi = str_ireplace($breaks_anonimisasi, "\r\n", $noPerkaraAnonimisasi);

    $pesan_anonimisasi="*==> Belum Upload Anonimisasi = $jum_anonimisasi |*\r\n$data_anonimisasi ";

    // =====================  DATA PERKARA SISA PAJAR KECUALI CT KABUL ============================

    $cek_sisa_panjar=mysql_query("SELECT 
                                  b.alur_perkara_id, 
                                  b.nomor_perkara,
                                  b.pihak1_text,
                                  b.jenis_perkara_text,
                                  REPLACE(b.para_pihak,'<br />',' ') AS pihak,
                                  c.tanggal_putusan, 
                                  @transaksi_terakhir:=(SELECT MAX(tanggal_transaksi) FROM sipp.perkara_biaya WHERE perkara_id = b.perkara_id ORDER BY tanggal_transaksi DESC) AS transaksi_terakhir,
                                  @enam_bulan:=DATE_ADD(@transaksi_terakhir, INTERVAL 90 DAY) AS batas_akhir,
                                  CONCAT((CASE WHEN(FLOOR(DATEDIFF(CURDATE(),@transaksi_terakhir)/30)) = 0 THEN '' ELSE (CONCAT(FLOOR(DATEDIFF(CURDATE(),@transaksi_terakhir)/30),' bulan ')) END),
                                  (CASE WHEN(MOD(DATEDIFF(CURDATE(),@transaksi_terakhir),30)) = 0 THEN '' ELSE (CONCAT(MOD(DATEDIFF(CURDATE(),@transaksi_terakhir),30),' hari')) END)) AS total_bulan,
                                  DATEDIFF(CURDATE(),@enam_bulan) AS total_batas,
                                  (SUM(CASE WHEN a.jenis_transaksi = '1' THEN a.jumlah ELSE 0 END)) AS total_biaya_masuk,
                                  (SUM(CASE WHEN a.jenis_transaksi = '-1' THEN a.jumlah ELSE 0 END)) AS total_biaya_keluar,
                                  ((SUM(CASE WHEN a.jenis_transaksi = '1' THEN a.jumlah ELSE 0 END))-(SUM(CASE WHEN a.jenis_transaksi = '-1' THEN a.jumlah ELSE 0 END))) AS sisa_biaya
                                  FROM sipp.perkara_biaya a, sipp.perkara b, sipp.perkara_putusan c 
                                  WHERE 
                                  a.tahapan_id = 10 
                                  AND (b.alur_perkara_id ='15' OR b.alur_perkara_id ='16') 
                                  AND b.perkara_id = c.perkara_id 
                                  AND a.perkara_id = b.perkara_id 
                                  AND (b.jenis_perkara_text != 'Cerai Talak' OR c.status_putusan_id != '62')
                                  AND
                                  (SELECT((SUM(CASE WHEN d.jenis_transaksi = '1' THEN jumlah ELSE 0 END))-(SUM(CASE WHEN d.jenis_transaksi = '-1' THEN d.jumlah ELSE 0 END)))
                                  FROM sipp.perkara_biaya d, sipp.perkara e, sipp.perkara_putusan f 
                                  WHERE b.perkara_id = d.perkara_id AND d.tahapan_id = 10 AND(e.alur_perkara_id ='15' OR e.alur_perkara_id ='16') AND e.perkara_id = f.perkara_id AND d.perkara_id = e.perkara_id
                                  GROUP BY d.perkara_id ORDER BY d.perkara_id DESC) <> 0 
                                              GROUP BY a.perkara_id ORDER BY a.perkara_id DESC");

    $jum_sisa_panjar=0;
    $noPerkarasisaPanjar='';

    while($sisa_panjar=mysql_fetch_array($cek_sisa_panjar)){
        $jum_sisa_panjar++;
        $totalSisaPanjar  = formatcurrency($sisa_panjar['sisa_biaya'],'IDR');
        $noPerkarasisaPanjar=$noPerkarasisaPanjar.$sisa_panjar['nomor_perkara'] . ' | Sisa Panjar : Rp. '.$totalSisaPanjar  .'<br/>';

    }

    $breaks_sisa_panjar = array("<br />","<br>","<br/>");  
    $data_sisa_panjar = str_ireplace($breaks_sisa_panjar, "\r\n", $noPerkarasisaPanjar);

    $pesan_sisa_panjar="*==> Perkara Putus Belum PSP = $jum_sisa_panjar |*\r\n$data_sisa_panjar ";

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

$notif_pesan = '*=== MONEV HARIAN SIPP PA TEMBILAHAN ===*'."\r\n\r\n".'A. PROSES PERKARA'."\r\n". $pesan_pmh ."\r\n". $pesan_phs."\r\n".$pesan_tunda_sidang."\r\n".$pesan_perkara_ecourt."\r\n".$pesan_relaas."\r\n".$pesan_pmh_ikrar_talak."\r\n".$pesan_petitum."\r\n".'B. PUTUSAN PERKARA'."\r\n".$pesan_bht."\r\n".$pesan_ac."\r\n".$pesan_doc_putusan."\r\n".$pesan_amar_putusan."\r\n".$pesan_amar_ikrar."\r\n".$pesan_minutasi."\r\n".$pesan_redaksi_putus."\r\n".$pesan_redaksi_belum_putus."\r\n".$pesan_cetak_ac_cg."\r\n".$pesan_cetak_ac_ct."\r\n".$pesan_anonimisasi."\r\n".$pesan_sisa_panjar."\r\n".$pesan_delegasi."\r\n\r\n".'Sinkron : '. date('d-m-Y H:i:s');   
 // echo $notif_pesan;

    if($jum>0 || $jum_phs>0 || $jum_tunda_sidang>0 || $jum_perkara_ecourt > 0 || $jum_relaas > 0 || $jum_pmh_ikrar_talak > 0 || $jum_petitum > 0 || $jum_bht > 0 || $jum_ac > 0 || $jum_doc_putusan > 0 || $jum_amar_putusan > 0 || $jum_amar_ikrar > 0 || $jum_minutasi > 0 || $jum_redaksi_putus > 0 || $jum_redaksi_belum_putus > 0 || $jum_cetak_ac_cg > 0 || $jum_cetak_ac_ct > 0 || $jum_anonimisasi > 0 || $jum_sisa_panjar > 0){
        // foreach($no_telpon_pimpinan as $a)
        // {
        $cek_pesan=mysql_query("select *  from notif_perkara_pa.notif_wa where no_tujuan IS NULL and isi_pesan='$notif_pesan'");
		$jum_pesan=mysql_num_rows($cek_pesan);
		if($jum_pesan < 1){
            $insert2=mysql_query("insert into notif_perkara_pa.notif_wa (no_tujuan, isi_pesan,status_kirim,detail_pengiriman,waktu_terkirim) values (null,'$notif_pesan','t',null,null)");
        }
            	
        // }
    }

?>