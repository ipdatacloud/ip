<?php

class Ip  {
  
    private $prefStart=array(); 
    private $prefEnd=array(); 
    private $endArr=array(); 
    private $addrArr=array(); 
    private $fp;
 
    static private   $instance= null;

    private    function __construct() { 
        $this->loadFile(); 
    }

    private  function loadFile(){
        $path='ipdatacloud.dat';
        $this->fp = fopen($path, 'rb');
        $fsize = filesize($path);
     
        $data = fread( $this->fp, $fsize); 

        for ($k = 0; $k < 256; $k++)
        {
            $i = $k * 8 + 4;           
            $this->prefStart[$k]=unpack("V",$data,$i)[1];
            $this->prefEnd[$k]=unpack("V",$data,$i+4)[1];               
        }

        $RecordSize =unpack("V",$data,0)[1];  

        for ($i = 0; $i <$RecordSize; $i++)
        {
            $p = 2052 + ($i * 9);         
            $this->endArr[$i] =unpack("V",$data,$p)[1];
      
            $offset =  unpack("V", $data[4 + $p]. $data[5 + $p]. $data[6 + $p]  . $data[7 + $p])[1];
            $length =unpack("C", $data[8 + $p])[1];
          
            fseek($this->fp, $offset);
            $this->addrArr[$i] =fread( $this->fp,  $length);
        }


    }

    function __destruct(){
        if ($this->fp !== NULL) {
            fclose($this->fp);
        }
    }

    private  function __clone() {}
    private  function __wakeup() {}
        
    public static function getInstance() {
        if (self::$instance instanceof Ip ) {           
            return self::$instance;
        }
        else{
          
            return self::$instance = new Ip();
          
        }
       
    }
    
    public function get($ip) {
        $val =sprintf("%u",ip2long($ip));
        $ip_arr = explode('.', $ip); 
        $pref = $ip_arr[0];
        $low = $this->prefStart[$pref];
        $high =  $this->prefEnd[$pref];
        $cur = $low == $high ? $low : $this->Search($low, $high, $val);
        return $this->addrArr[$cur];
    }

    private function Search($low, $high, $k) {
        $M = 0;
        while ($low <= $high) {
            $mid = floor(($low + $high) / 2);
            $endipNum = $this->endArr[$mid];           
            if ($endipNum >= $k) {
                $M = $mid;
                if ($mid == 0) {
                    break;
                }
                $high = $mid - 1;
            } else $low = $mid + 1;
        }
        return $M;
    }
   
    
}
 
?>
