<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/6
 * Time: 11:42
 */

namespace App;

use Predis\Client;

class Helper
{
    public static function redisClient()
    {
        $redis = new Client([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ]);
        return $redis;
    }

    public static function getCache($key){
        $result=self::redisClient()->get($key);
        if(!empty($result)){
            return unserialize($result);
        }else{
            return '';
        }
    }

    public static function setCache($key,$value,$time=30){
        $value=serialize($value);
        return self::redisClient()->setex($key,$time,$value);
    }

    public static function getCSVtoArray($path)
    {
        $array=array();
        if (!file_exists($path)) {
            exit("文件".$path."不存在");
        }
        $file = fopen($path,'r');
        while(! feof($file))
        {
            $data= trim(fgets($file));
            if($data!=''){
                $data=explode(',',$data);
                foreach ($data as $i=>$v){
                    $v=trim($v,'"');
                    $data[$i]=iconv('GBK','utf-8',trim($v));
                }
                $array[] = $data;
            }
        }
        fclose($file);
        return $array;
    }

    public static function curl_url($url, $data = array())
    {
        $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if ($data) {
            if (is_array($data)) {
                curl_setopt($ch, CURLOPT_POST, 1);
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data))
                );
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if ($ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public static function getSign($data)
    {
        if (isset($data['sign'])) {
            unset($data['sign']);
        }
        if (isset($data['data'])) {
            foreach ($data['data'] as $i => $v) {
                if (is_array($v)) {
                    ksort($data['data'][$i]);
                }
            }
        }
        ksort($data);
        $jsonStr = json_encode($data);
        $str = strtoupper(md5($jsonStr . 'secret'));
        return $str;
    }
    public static function betweenDays ($date1, $date2)
    {
        $second1 = strtotime($date1);
        $second2 = strtotime($date2);
        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }

    public static function is_mobile_request(){
        $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
        $mobile_browser = '0';
        if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
            $mobile_browser++;
        if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))
            $mobile_browser++;
        if(isset($_SERVER['HTTP_X_WAP_PROFILE']))
            $mobile_browser++;
        if(isset($_SERVER['HTTP_PROFILE']))
            $mobile_browser++;
        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
        $mobile_agents = array(
            'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
            'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
            'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
            'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
            'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
            'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
            'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
            'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
            'wapr','webc','winw','winw','xda','xda-'
        );
        if(in_array($mobile_ua, $mobile_agents))
            $mobile_browser++;
        if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
            $mobile_browser++;
        // Pre-final check to reset everything if the user is on Windows
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
            $mobile_browser=0;
        // But WP7 is also Windows, with a slightly different characteristic
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
            $mobile_browser++;
        if($mobile_browser>0)
            return true;
        else
            return false;
    }
    
    public static function getSystemParam($code)
    {
        $value = app('\App\Model\System')->getCode($code);
        if ($code == 'convert_rate') {
            $value=(float)$value;
            if ($value==0) {
                $value = 2.52;
            }
        }
        return $value;
    }

    public static function QRcode($txt,$filePath='goods',$fileName=0,$level='M')
    {
        if(is_int($fileName)){
            $img_url="/data/QRcode/{$filePath}/".ceil(intval($fileName)/2000)."/";
        }else{
            $img_url="/data/QRcode/{$filePath}/";
        }
        $file_dir = ROOT . "/public".$img_url;
        if (!is_dir($file_dir)) {
            mkdir($file_dir, 0777, true);
        }
        $file_name=$fileName.'.png';
        $file_path=$file_dir.$file_name;
        $img_url.=$file_name;
        if(!file_exists($file_path)){
            QRcode::png($txt,$file_path,$level,4,2);
        }
        return $img_url;
    }


    public static function log($name='error',$data)
    {
        $path = ROOT . "/public/data/logs/";
        if (!file_exists($path)) {
            mkdir($path,0777,true);
        }
        $myfile = fopen($path.$name.'_'.date('Ym').".txt", "a+");
        if(is_array($data)){
            $data=json_encode($data);
        }
        $file = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
        fwrite($myfile, '【'.date('Y-m-d H:i:s').'】'."\t file:{$file}\t".$data."\r\n");
        fclose($myfile);
    }

    public static function getFormat($num,$is_RMB=true)
    {
        $num=(float)$num;
        if($num!=0){
            return $is_RMB?'￥'.$num:$num;
        }
    }

    public static function exportExcel($name,$title,$data)
    {
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        if (!\PHPExcel_Settings::setCacheStorageMethod($cacheMethod)) {
            die($cacheMethod . " 缓存方法不可用" . EOL);
        }
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
        $abc='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if(count($title)<10){
            foreach ($title as $i=>$item){
                $cell=substr($abc,$i,1);
                //$objActSheet->getColumnDimension('A')->setWidth(20);
                $objActSheet->getColumnDimension($cell)->setWidth(20);
            }
        }
        foreach ($title as $i=>$item){
            $cell=substr($abc,$i,1).'1';
            $objActSheet->setCellValue($cell, $item);
            $objActSheet->getRowDimension(1)->setRowHeight(30);
            $objActSheet->getStyle($cell)->getFont()->setSize(12);
            $objActSheet->getStyle($cell)->getFont()->setBold(true);
            $objActSheet->getStyle($cell)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objActSheet->getStyle($cell)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
        unset($title);
        foreach($data as $i=>$row){
            $num=$i+2;
            //Excel的第A列，uid是你查出数组的键值，下面以此类推
            foreach ($row as $j=>$col){
                $objActSheet->setCellValue(substr($abc,$j,1).$num, $col);
            }
            $objActSheet->getRowDimension($num)->setRowHeight(22);
            unset($data[$i]);
        }
        unset($data);
        $objActSheet->setTitle($name);
        header('Content-Type: applicationnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$name.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    public static function filter_utf8_char($ostr){
        preg_match_all('/[\x{FF00}-\x{FFEF}|\x{0000}-\x{00ff}|\x{4e00}-\x{9fff}]+/u', $ostr, $matches);
        $str = join('', $matches[0]);
        if($str==''){   //含有特殊字符需要逐個處理
            $returnstr = '';
            $i = 0;
            $str_length = strlen($ostr);
            while ($i<=$str_length){
                $temp_str = substr($ostr, $i, 1);
                $ascnum = Ord($temp_str);
                if ($ascnum>=224){
                    $returnstr = $returnstr.substr($ostr, $i, 3);
                    $i = $i + 3;
                }elseif ($ascnum>=192){
                    $returnstr = $returnstr.substr($ostr, $i, 2);
                    $i = $i + 2;
                }elseif ($ascnum>=65 && $ascnum<=90){
                    $returnstr = $returnstr.substr($ostr, $i, 1);
                    $i = $i + 1;
                }elseif ($ascnum>=128 && $ascnum<=191){ // 特殊字符
                    $i = $i + 1;
                }else{
                    $returnstr = $returnstr.substr($ostr, $i, 1);
                    $i = $i + 1;
                }
            }
            $str = $returnstr;
            preg_match_all('/[\x{FF00}-\x{FFEF}|\x{0000}-\x{00ff}|\x{4e00}-\x{9fff}]+/u', $str, $matches);
            $str = join('', $matches[0]);
        }
        return $str;
    }
}