<?
include "../conf.php";
$id = $_GET['id'];
$update = array(
		'is_del' => 2
	);
	
	$res = $db->row_update('gushi',$update, "id = '$id' ");
	
	//$res = $db->row_delete('gushi',"id = '$gu_id' AND umd5 = '$f_user' ");

	if($res){
	echo 'ɾ���ɹ�'; 
	}else{
	echo 'ɾ��ʧ��'; 
	}
?>