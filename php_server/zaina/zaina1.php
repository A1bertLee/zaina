<?
include('conf.php'); 
$action = $_GET['action'];//action = u Ϊ��������
$umd5 = $_GET['user'];//�û�Ψһid md5����

$load = $_GET['load'];//��ȡ��ǰ���ط�ʽ-��һ�μ��أ�f����ˢ�£�r�������ظ��ࣨl��

//$Latitude = $_GET['Latitude'];//γ��
//$Longitude = $_GET['Longitude'];//����
$jingweidu = $_GET['jingweidu'];//��γ��
$jiedao = $_GET['jiedao'];

//�ж��Ƿ񱻺�����
is_black($umd5);
/***************�Ա�ɸѡ****************/
$sex = $_GET['sex'];//������Ա�
if($sex == "1"){
$sqlsex = " AND `user`.sex = '1'  ";
}else if($sex == "2"){
$sqlsex = " AND `user`.sex = '2'  "; 
}else{
$sqlsex = ''; 
}
/*******************************/

$page = $_GET['page'];

//�û��汾�ţ��ӹ��°汾����У�1.5��
$version = $_GET['v'];
//$version = "1.1";
$pagenum = 10;//ÿһҳ��ʾ����Ŀ

if(empty($umd5)){
json('no','������ʧ',"");
}

if(!empty($action)){
	if($action=='n'){//�������ߵ�
		if($load=='f'){//��һ�μ���
			if(!empty($jingweidu)){
				if(empty($jiedao)){
					$jwdarr = explode(',',$jingweidu);
					$baidulink = 'http://api.map.baidu.com/geocoder/v2/?ak=O2IwdiijdOaQ3KQA9pdmMw8c&location='.$jwdarr[1].','.$jwdarr[0].'&output=json&pois=0';
					$jwdjson = get($baidulink);
					$jwdstr = (array)json_decode($jwdjson);
					$jiedao = $jwdstr['result']->formatted_address;
				}else if($jiedao == ',,,,,'){
					$jwdarr = explode(',',$jingweidu); 
					$googlelink = 'http://maps.google.cn/maps/api/geocode/json?latlng='.$jwdarr[1].','.$jwdarr[0].'&sensor=true&language=zh-CN';
					$jwdjson_google = get($googlelink);
					$jwdstr_google = (array)json_decode($jwdjson_google);
					$jiedao = $jwdstr_google['result']->formatted_address;
					
				
				}
				//��ȡ�ӵ�λ��
				$jiedao = preg_replace('/^([^\d]+).*/', '$1', $jiedao);
				//�ӻ����������ȡ���û��ϴεĵص�
				//�����ж��Ƿ���һ���ص��ύ������
				$mem = new Memcache; 
				$mem->connect("172.18.1.23", 12000); 
				$userjiedao  = $mem->get($umd5.'jiedao');
				if($userjiedao != $jiedao){//���������������ݿ�
					/*******************��ȡ���˵�geohash********************/
					$jwdarrtmp = explode(',',$jingweidu);
					//include('./lib/geohash.class.php');
					require_once('./lib/geohash.class.php');
					 $geohash = new Geohash;
					 //�õ�����hashֵ
					 $hash = $geohash->encode($jwdarrtmp[1], $jwdarrtmp[0]);
					// echo $hash;
					//ȡǰ׺��ǰ׺Լ����ΧԽС
					//$prefix = substr($hash, 0, 5);
					//ȡ�����ڰ˸�����
					//$neighbors = $geohash->neighbors($prefix);
					/***************************************/
					$insert = array(
						'geo' => $jingweidu ,
						'time' => time(),
						'ip' => $_SERVER["REMOTE_ADDR"],
						'jiedao' => safeEncoding($jiedao),
						'geohash' =>  $hash,
						'umd5' => $umd5 
					);
					
					$mem->set($umd5.'jiedao', $jiedao, 0, 600);
					
					$db->row_insert("zaina",$insert); 
					$update =array(
						'zaina' =>  $jiedao,
						'version' => $version
					);
					$db->row_update('user',$update," umd5 = '$umd5' ");
				}
			}
			//	SELECT zaina.time ,zaina.jiedao,zaina.umd5,`user`.age ,`user`.sex ,`user`.headurl ,`user`.`name` FROM zaina LEFT JOIN `user` ON (zaina.umd5=`user`.umd5)WHERE zaina.time > 1408204280
			//��������ÿ��һ��ʱ�仺��һ�Σ��������ļ��У���ҳ ʱ��
				//print_r($res);
			$res = $db->row_query("SELECT zaina.time ,zaina.jiedao,zaina.umd5,`user`.age ,`user`.sex ,`user`.headurl ,`user`.province,`user`.city,`user`.`name`
											FROM zaina  LEFT JOIN `user`  ON (zaina.umd5=`user`.umd5) WHERE zaina.time > 1410878567   $sqlsex   ORDER BY zaina.id DESC  LIMIT  0,10 ");
				json('yes','',$res);
		}else if($load=='r'){
			$mem = new Memcache; 
			$mem->connect("172.18.1.23", 12000); 
			if(!empty($jingweidu)){
					if(empty($jiedao)){
						$jwdarr = explode(',',$jingweidu);
						$baidulink = 'http://api.map.baidu.com/geocoder/v2/?ak=O2IwdiijdOaQ3KQA9pdmMw8c&location='.$jwdarr[1].','.$jwdarr[0].'&output=json&pois=0';
						$jwdjson = get($baidulink);
						$jwdstr = (array)json_decode($jwdjson);
						$jiedao = $jwdstr['result']->formatted_address;
					}
				//��ȡ�ӵ�λ��
				$jiedao = preg_replace('/^([^\d]+).*/', '$1', $jiedao);
				//�ӻ����������ȡ���û��ϴεĵص�
				//�����ж��Ƿ���һ���ص��ύ������ 
				$userjiedao  = $mem->get($umd5.'jiedao');
				if($userjiedao != $jiedao){//���������������ݿ�
				
					$insert = array(
						'geo' => $jingweidu ,
						'time' => time(),
						'ip' => $_SERVER["REMOTE_ADDR"],
						'jiedao' => safeEncoding($jiedao),
						'umd5' => $umd5 
					);
					$db->row_insert("zaina",$insert); 
					
				
					$mem->set($umd5.'jiedao', $jiedao, 0, 600);
					
					$update =array(
						'zaina' =>  $jiedao
					);
					$db->row_update('user',$update," umd5 = '$umd5' ");
				}
			}
			
			$zainares  = $mem->get($sex.'zaina');
			if(empty($zainares)){
			$res = $db->row_query("SELECT zaina.time ,zaina.jiedao,zaina.umd5,`user`.age ,`user`.sex ,`user`.headurl ,`user`.province,`user`.city,`user`.`name`
											FROM zaina  LEFT JOIN `user`  ON (zaina.umd5=`user`.umd5) WHERE 1=1    $sqlsex   ORDER BY zaina.id DESC  LIMIT  0,10 ");
				$mem->set($sex.'zaina', $res, 0, 60);
			}else{
				$res = $zainares;
			}
			json('yes','',$res);
		}else if($load=='l'){
				if($page<=0){
					$page = 1;
				}if($page>20){
					json('no','ʱ���Ѿ�Զ','');
				}
				$start = $page*$pagenum;
				//$last = ($page+1)*$pagenum;
				$res = $db->row_query("SELECT zaina.time ,zaina.jiedao,zaina.umd5,`user`.age ,`user`.sex ,`user`.headurl ,`user`.province,`user`.city,`user`.`name`
											FROM zaina  LEFT JOIN `user`  ON (zaina.umd5=`user`.umd5) WHERE zaina.time > 1408204280  $sqlsex   ORDER BY zaina.id DESC  LIMIT $start,$pagenum ");
				if(empty($res)){
				json('no',"�����������Ե",$res);
				}else{
				json('yes',"",$res);
				}
				
		}

		
	}else{
		json('no','ȱ�ٲ���','');
	}
}else{
	json('no','ȱ�ٲ���','');
}