<?php

/**
 *  +-------------+-----------------+-----------+------------------+
 *  |   Author    |      Date       |  version  |   E-mail         |
 *  +-------------+-----------------+-----------+------------------+
 *  |  Tao lifeng | 2018/3/28 14:48 |   1.0     | 742592958@qq.com |
 *  +-------------+-----------------+-----------+------------------+
 *  |                       Abstract                               |
 *  +--------------------------------------------------------------+
 *  |   This is an image processing class, can be compressed image,|
 *  |   verification code and other operations.                    |
 *  +--------------------------------------------------------------+
 */


namespace GD\tools;


class Image
{

    //构造时的图片资源标识
    protected $image;

    //新生成的资源标识
    protected $new_image;

    //所有图片类型
    protected $types = [
        1 => 'GIF',
        2 => 'JPG',
        3 => 'PNG',
        4 => 'SWF',
        5 => 'PSD',
        6 => 'BMP',
        7 => 'TIFF',
        8 => 'TIFF',
        9 => 'JPC',
        10 => 'JP2',
        11 => 'JPX',
        12 => 'JB2',
        13 => 'SWC',
        14 => 'IFF',
        15 => 'WBMP',
        16 => 'XBM'
    ];

    //老图的宽度
    protected $old_width;

    //老图的高度
    protected $old_height;

    //老图的大小
    protected $old_byte;

    //保存后图片的名称
    protected $image_name = "";

    //验证码code存放处
    protected $auth_code;

    //配置文件 可以根据需求更改
    //验证码都需要用到
    protected $config = [
        'width' => 150,
        'height' => 60,
        'size' => 22,
        'x' => 0,
        'y' => 40,
        'len' => 4,
        'offset' => 30,
        'fontStyle' => 'font/MONACO.TTF',
        'lineCount'=>10
    ];


    /**
     * Image constructor.
     * @param null $path
     * @throws \Exception
     */
    public function __construct($path = null)
    {
        if ($path !== null) {
            //对图片进行压缩时传入 path
            if (!is_file($path)) {
                throw new \Exception('not find file');
            }

            $img_name = substr(strrchr($path, '/'), 1);

            list($width, $height, $type, $byte) = getimagesize($path);

            $this->image = $this->createImageObj($type,$path);

            $this->old_height = $height;
            $this->old_width = $width;
            $this->old_byte = $byte;
            $this->image_name = $img_name;
        }

    }

    /**
     * 图片压缩处理
     * @param int $width 理想宽度
     * @param int $height 理想高度
     * @param int $type type = 1 正常压缩 type = 2 等比宽度压缩 type = 3 等比宽高压缩
     * @return $this
     */
    public function compress($width, $height, $type = 1)
    {
        switch ($type) {
            case 1:
            default:
                $new_image = imagecreatetruecolor($width, $height);
                imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->old_width, $this->old_height);
                break;
            case 2:
                $ratio = $this->old_width / $width;
                // 1000 / 100 = 10
                $width = $this->old_width / $ratio;
                $height = $this->old_height / $ratio;
                $new_image = imagecreatetruecolor($width, $height);
                imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->old_width, $this->old_height);
                break;
            case 3:
                $diff1 = $this->old_height - $height;
                $diff2 = $this->old_width - $width;

                if ($diff1 > $diff2) {
                    $ratio = $this->old_height / $height;
                }else{
                    $ratio = $this->old_width / $width;
                }

                $new_width = $this->old_width / $ratio;
                $new_height = $this->old_height / $ratio;
                $new_image = imagecreatetruecolor($width, $height);
                $back_color = imagecolorallocate($new_image, 255, 255, 255);
                imagefill($new_image, 0, 0, $back_color);
                imagecopyresampled($new_image, $this->image, ($width - $new_width) / 2, ($height - $new_height) / 2, 0, 0, $new_width, $new_height, $this->old_width, $this->old_height);
                break;
        }
        $this->new_image = $new_image;
        return $this;
    }


    /**
     * 将图片的资源标识放入到文件系统
     * @param string $path_dir 保存路径
     * @param int $type type = 2 jpg type = 3 png type 按照 types 表
     * @return void
     */
    public function save($path_dir, $type = 2)
    {
        $ds = substr($path_dir, -1);
        if ($ds !== '/' && $ds !== '\\') {
            $path_dir .= '/';
        }
        $salt_name =  openssl_digest($this->image_name . time() . rand(1000, 9999), 'sha1') . ".{$this->types[$type]}";
        switch ($type) {
            case 2:
            default:
                imagejpeg($this->new_image, $path_dir .$salt_name);
                break;
            case 3:
                imagepng($this->new_image, $path_dir . $salt_name);
                break;
        }
        $this->image_name = $salt_name;
        imagedestroy($this->new_image);
    }

    /**
     * 获取图片名称
     * @return bool|string
     */
    public function getImageName()
    {
        return $this->image_name;
    }


    /**
     * 验证码
     * @param array $config 配置列表具体配置
     * @return $this
     */
    public function authCode($config = [])
    {

        $c = array_replace($this->config, $config);

        $imgBg = imagecreatetruecolor($c['width'], $c['height']);
        $brush = imagecolorallocate($imgBg, 255, 255, 255);
        $line = imagecolorallocate($imgBg, 150, 150, 150);
        imagefill($imgBg, 0, 0, $brush);

        $strList = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $code = null;
        //制作验证码
        for ($i = 1; $i <= $c['len']; $i++) {
            $brush = imagecolorallocate($imgBg, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            $char = $strList[mt_rand(0, 61)];
            imagettftext($imgBg, $c['size'], mt_rand(-60, 60), $c['x'] + $i * $c['offset'], $c['y'], $brush, $c['fontStyle'], $char);

            $code .= $char;
        }
        for ($i = 0; $i < $c['lineCount']; $i++){
            imageline($imgBg, mt_rand(0, $c['width'] / 2), mt_rand(0, $c['height']), mt_rand($c['width'] / 2, $c['width']), mt_rand(0, $c['height']), $line);
        }
        $this->new_image = $imgBg;
        $this->auth_code = $code;
        return $this;
    }

    /**
     * 将资源标识符展示在前台页面上
     * @return void
     */
    public function show(){
        header('content-type:image/jpeg');
        imagejpeg($this->new_image);
        imagedestroy($this->new_image);
    }


    /**
     * 水印
     * @param string $content 如果type是1 则输入文字 如果type是2 则输入图片路径
     * @param int $type type 1 文字水印 2 图片水印
     * @param array $config
     * @return $this
     * @throws \Exception
     */
    public function mask($content = '' , $type = 1  , $config = ['x'=>10,'y'=>32]){
        $c = array_replace($this->config, $config);
        switch ($type){
            case 1:
                $black = imagecolorallocate($this->image, 100, 100, 100);
                imagettftext($this->image, $c['size'], 0, $c['x'], $c['y'] , $black,$c['fontStyle'],$content );
                break;
            case 2:
                if(!$arr = $this->checkImage($content)){
                    throw new \Exception('This is not an image path');
                }
                $src_image = $this->createImageObj($arr[2],$content);
                imagecopy($this->image , $src_image , 0 , 0 , 0 , 0 , $arr[0] , $arr[1] );
                break;
        }
        $this->new_image =& $this->image;
        return $this;
    }

    /**
     * 验证是否是图片
     * @param string $path 图片路径
     * @return array|bool 如果是图片 , 返回图片宽度, 高度, type类型
     */
    public function checkImage($path){
        if(!is_file($path)){return false;}
        $arr = getimagesize($path);
        if(in_array($arr[2] , array_keys($this->types))){
            return $arr;
        }
        return false;
    }

    /**
     * 创建对应资源
     * @param int $type 类型
     * @param string $path 图片路径
     * @return resource
     * @throws \Exception
     */
    public function createImageObj($type , $path){
        switch ($type) {
            case 2:
                return imagecreatefromjpeg($path);
                break;
            case 3:
                return imagecreatefrompng($path);
                break;
            default:
                throw new \Exception('this is type is not supported');
        }
    }
}

//图片压缩后保存 , 也可以将save()方法变成show()方法直接展现
//$img = new Image('../temp/03.jpeg');

/*
$img->compress(800,500,3)->save('./');
echo $img->getImageName();  //e24bc2d37d9c34e231b3843d97cfd8142606111a.JPG
*/
/*
//马赛克
$img->mask('mask test' , 1)->save('./');
*/
//$img->authCode([
//    'width' => 500,
//    'height' => 500,
//    'size' => 40,
//])->show();