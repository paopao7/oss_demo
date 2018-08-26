<?php
namespace Home\Controller;
use Think\Controller;
use OSS\OssClient;

class IndexController extends Controller {
	function _initialize() {
        require_once './vendor/autoload.php';
    }
    /***********************************************/
	/***********************************************/
	/***********************************************/
	/***********************************************/
	/*
	 *OSS已使用composer安装好，无需再次安装
	 *使用前请先去以下路径配置相关参数
	 *tp3/vendor/aliyuncs/oss-sdk-php/src/OSS/OssClient.php
	*/
	/***********************************************/
	/***********************************************/
	/***********************************************/

    
    ////////////////////////////////////////////以下为我封装的方法//////////////////////////////////////

    //上传文件
    /* int $fileType 上传文件类型(0:图片 1：文件)
     * string $file_name 文件名
     * file $file 上传文件对象
     * string $upType 自定义上传文件类型，使用“,”分割 不传则读取配置表信息
     * boolean $thumb_flag 是否启用缩略图功能
     * int $tWidth 缩略图宽度
     * int $tHeight 缩略图高度
     * string $thumb 缩略图前缀
     */
    public function uploadFile($fileType=0,$file_name='',$file='',$upType=0,$thumb_flag=false,$tWidth=0,$tHeight=0,$thumb=false){
        if($file_name) {
            $file = array();
            $file[$file_name]['name'] = $_FILES[$file_name]['name'];
            $file[$file_name]['type'] = $_FILES[$file_name]["type"];
            $file[$file_name]['tmp_name'] = $_FILES[$file_name]["tmp_name"];
            $file[$file_name]['error'] = $_FILES[$file_name]["error"];
            $file[$file_name]['size'] = $_FILES[$file_name]["size"];
        }

        //文件类型
        $ftype = '.jpeg,.jpg,.png';

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize  = C('FILEMAXSIZE') ; // 设置附件上传大小
        $upload->exts  = $upType?explode(',',str_replace('.','',$upType)):explode(',',str_replace('.','',$ftype)); // 设置附件上传类型
        $upload->rootPath = './';
        $upload->savePath =  'uploads/';
        $upload->replace = true;
        $upload->saveName = 'uniqid';//上传文件名的保存规则

        $file_key = key($file);

        if($file){
            $infor = $upload->upload($file);
        }else{
            $infor = $upload->upload();
        }

        if(!$infor) { // 上传错误提示错误信息
            $data['status'] = false;
            $data['respond'] = "";
            $data['other'] = "";
            $data['code'] = "";
            $data['message'] = $upload->getError();

            null_handle($data);
            echo json_encode($data,JSON_UNESCAPED_UNICODE);exit;
        }else{
            if($thumb_flag){
                //生成缩略图
                $img = new Image();
                //大图片的路径
                $big_img = $upload->rootPath.$infor[$file_key]['savepath'].$infor[$file_key]['savename'];
                //打开大图片
                $img->open($big_img);
                //设置图片大小
                $img->thumb($tWidth,$tHeight);
                //设置绝对路径
                $small_img = $upload->rootPath.$infor[$file_key]['savepath'].$thumb.$infor[$file_key]['savename'];
                //保存
                $img->save($small_img);
            }

            return $infor;
        }
        return false;
    }

    /*
     * 上传文件至阿里云OSS
     * $type:文件类型
     * $tmp_name：类似文件名
     * */
    public function upload_file_to_oss($type,$tmp_name){
        //文件名
        $file_name = $this->getFileName();

        //获取文件后缀包括.
        $suffix = substr($tmp_name, strrpos($tmp_name, '.'));

        try {
            $ossClient = new OssClient();
            $result = $ossClient->uploadFile('itinfor-source',$type.'/'.date('Y-m-d').'/'.$file_name.$suffix,$tmp_name);

            return '/'.$type.'/'.date('Y-m-d').'/'.$file_name.$suffix;//最前面的"/"可去掉
        } catch (OssException $e) {
            print $e->getMessage();
        }
    }

    //随机生成文件名
    private function getFileName(){
        $rand=substr(md5(rand()),0,6);
        return time().$rand;
    }

    //判断oss文件是否存在
    protected function file_exit($object){
		$ossClient = new OssClient();

        $object = substr($object,1);//若objact前不带"/"则无需执行该方法
        $exit = $ossClient->doesObjectExist('itinfor-source',$object);

        return $exit;
    }

    //删除OSS文件
    protected function delete_oss_object($object){
        $ossClient = new OssClient();

        $object = substr($object,1);//若objact前不带"/"则无需执行该方法
        $ossClient->deleteObject('itinfor-source',$object);
    }



    /////////////////////////////////////////////以下为实际测试代码/////////////////////////////////
    public function index(){
        $this->display();
    }

    //上传提交
    public function upload(){
        if ($_FILES['image']['size'] != 0) {
            //上传本地
            $file_image = $this->uploadFile(1, 'image');

            $image_path = trim($file_image['image']['savepath'] . $file_image['image']['savename']);
dump($image_path);
            //上传阿里云OSS
            if ($file_image) {
                $file_image_oss = $this->upload_file_to_oss('images', $image_path);
dump($file_image_oss);                
            }

            //删除原始文件
            if ($file_image_oss) {
                unlink($image_path);
                
            }
        }
    }

    //判断文件是否存在
    public function exits(){
    	$result = $this->file_exit("");

    	dump($result);
    }
    //删除oss文件
    public function delete(){
    	$result = $this->delete_oss_object("");
dump($result);    	
    }
}