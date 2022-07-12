<?php
/* 
PHP Version 7+
*/
class Ip
{

    private $prefStart = array();
    private $prefEnd = array();
    private $endArr = array();
    private $fp;
    private $data;

    static private $instance = null;

    private function __construct()
    {
        $this->loadFile();
    }

    private function loadFile()
    {
        $path = 'ipdatacloud.dat';
        $this->fp = fopen($path, 'rb');
        $fsize = filesize($path);

        $this->data = fread($this->fp, $fsize);

        for ($k = 0; $k < 256; $k++) {
            $i = $k * 8 + 4;
            $this->prefStart[$k] = unpack("V", $this->data, $i)[1];
            $this->prefEnd[$k] = unpack("V", $this->data, $i + 4)[1];
        }

    }

    function __destruct()
    {
        if ($this->fp !== NULL) {
            fclose($this->fp);
        }
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public static function getInstance()
    {
        if (self::$instance instanceof Ip) {
            return self::$instance;
        } else {

            return self::$instance = new Ip();

        }

    }

    private function getByCur($i)
    {
        $p = 2052 + (intval($i) * 9);

        $offset = unpack("V", $this->data[4 + $p] . $this->data[5 + $p] . $this->data[6 + $p] . $this->data[7 + $p])[1];
        $length = unpack("C", $this->data[8 + $p])[1];

        fseek($this->fp, $offset);
        return fread($this->fp, $length);
    }

    public function get($ip)
    {
        $val = sprintf("%u", ip2long($ip));
        $ip_arr = explode('.', $ip);
        $pref = $ip_arr[0];
        $low = $this->prefStart[$pref];
        $high = $this->prefEnd[$pref];
        $cur = $low == $high ? $low : $this->Search($low, $high, $val);
        if ($cur == 100000000) {
            return "无信息";
        }
        return $this->getByCur($cur);
    }

    private function Search($low, $high, $k)
    {
        $M = 0;

        for ($i = $low; $i < $high + 1; $i++) {
            $p = 2052 + ($i * 9);
            $this->endArr[$i] = unpack("V", $this->data, $p)[1];
        }
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
