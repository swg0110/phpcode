<?php
namespace app\admin\controller;
use app\model\User;
use Think\Db;

class Cdistributionmercapdetails extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    //    获取列表页数据
    public function get_list(){
        $model = $this->model;
        //条件
        $query = function ($query){
            //关键字搜索
            $key = input('get.key');
            $type= input('get.type');
            if ($key){
                $query->where('store_name','like',"%$key%");
            }
             if($type>0){
                 $query->where('mcd_type','=',$type);
             }
        };

        //排序、分页
        $model->fill_order_limit();

        $list = $model->where($query)->order('id desc')->select();
        foreach ($list as &$item) {
            $user=User::get($item['user_id']);
            $item['nickname']=$user['nickname'];
            if($item['mcd_type']==1){
                $item['mcd_type_z']='分销订单收入';
            }else if($item['mcd_type']==2){
                $item['mcd_type_z']='提现';
            }else if($item['mcd_type']==3){
                $item['mcd_type_z']='审核失败返还收入';
            }else if($item['mcd_type']==5){
                $item['mcd_type_z']='提现失败返还收入';
            }
        }
        return [
            'code'=>0,
            'count'=>$model->where($query)->count(),
            'data'=>$list,
            'msg'=>'',
        ];
    }

}
