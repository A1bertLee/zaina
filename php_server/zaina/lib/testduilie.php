<?
include('duilie.php');
$key = "duilie";
$value = "duilie_value";
for($i=0;$i<100;$i++){
  //QMC::input($key, $i);//д�����
}

for($i=0;$i<100;$i++){
	$list = QMC::output($key,1);//��ȡ����
	print_r($list);
}

?>