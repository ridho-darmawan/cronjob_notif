<?php
$url='localhost';
$username='root';
$password='4dminpatbh';
$conn=mysqli_connect($url,$username,$password,"db_aslipatbh");
?>
<?php

$sql = "DELETE FROM tbl_antrian WHERE id !='0'";
if (mysqli_query($conn, $sql)) {
   } else {
   }
mysqli_close($conn);
?>