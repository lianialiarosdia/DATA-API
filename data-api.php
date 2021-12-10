<?php 
   
   require "koneksidb.php";

   $ambilrfid	 = $_GET["rfid"];
   $ambilnamatol	 = $_GET["namatol"];
   
   date_default_timezone_set('Asia/Jakarta');
   $tgl=date("Y-m-d G:i:s");
   
   
		
		
   //LOGIKA Pembayaran
   $ambilsaldo = query ("SELECT * FROM tb_daftarrfid WHERE rfid = '$ambilrfid'")[0];
   $saldolama = $ambilsaldo['saldoawal'];
   $cekrfid = $ambilsaldo['rfid'];
   
   $ambilharga = query("SELECT * FROM tb_tol WHERE namatol = '$ambilnamatol'")[0];
   $sqlbayar = $ambilharga['bayar'];
   
   if($saldolama<$sqlbayar){
   $situasi = "KURANG";
   }else{
   $situasi = "CUKUP";}
   
   //UPDATE DATA REALTIME PADA TABEL tb_monitoring
		$sql      = "UPDATE tb_monitoring SET tanggal	= '$tgl', rfid	= '$ambilrfid', namatol	= '$ambilnamatol', situasi='$situasi'";
		$koneksi->query($sql);
   
   if($cekrfid==null){
			//MEMBUAT DATA UNTUK DIJADIKAN JSON DAN DIKIRIM KE ARDUIO
			$datadumy=array('id'=>'0','rfid'=>$ambilrfid,'nama'=>'Tidak Terdaftar','alamat'=>'Tidak Terdaftar','telepon'=>'Tidak Terdaftar','saldoawal'=>'Tidak Terdaftar','tanggal'=>$tgl,'namatol'=>$ambiltol,'situasi'=>$situasi);
			$result = json_encode($datadumy); //MENJADIKAN JSON DATA
			echo $result;
		}
   
   if($situasi =="CUKUP" and $cekrfid!=null){
     $saldobaru = $saldolama - $sqlbayar;
     $sql      = "UPDATE tb_daftarrfid SET saldoawal='$saldobaru' WHERE rfid='$ambilrfid'";
  	 $koneksi->query($sql);
		
    //INSERT DATA REALTIME PADA TABEL tb_save  	
	  
		$sqlsave = "INSERT INTO tb_simpan (tanggal, rfid, saldo, bayar, saldoahir, namatol) VALUES ('" . $tgl . "', '" . $ambilrfid . "', '" . $saldolama . "', '" . $sqlbayar . "', '" . $saldobaru . "','" . $ambilnamatol . "')";
		$koneksi->query($sqlsave);   

		//MENJADIKAN JSON DATA
		//$response = query("SELECT * FROM tb_monitoring")[0];
		$response = query("SELECT * FROM tb_daftarrfid,tb_monitoring WHERE tb_daftarrfid.rfid='$ambilrfid'" )[0];
    //$response = query("SELECT * FROM tb_simpan,tb_monitoring WHERE tb_simpan.rfid='$ambilrfid'" )[0];
      	$result = json_encode($response);
     	echo $result;
}
  else if ($situasi=="KURANG" and $cekrfid!=null){
			//MENGABIL DATA UNTUK DIJADIKAN JSON DAN DIKIRIM KE ARDUIO
			$response = query("SELECT * FROM tb_daftarrfid,tb_monitoring WHERE tb_daftarrfid.rfid='$ambilrfid'" )[0];
			$result = json_encode($response); //MENJADIKAN JSON DATA
			echo $result;
			//echo $ceksaldo;
			//echo $cekrfid;
		}


 ?>