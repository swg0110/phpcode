<?php
/**
 * 微商城公告模块微站定义
 *
 * @author 微擎团队
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
include IA_ROOT."/addons/ymmf_sun_plugin_distribution/func/func.php";

class ymmf_sun_plugin_distributionModuleSite extends WeModuleSite {

	//分销设置
	public function doWebdistribution_set() {
		global $_GPC, $_W;
		$settype = intval($_GPC["settype"]);
		$todo = $_GPC["todo"];
		$tid = $_GPC["tid"];

		$info = pdo_get('ymmf_sun_distribution_set', array('uniacid' => $_W['uniacid']));
		$info["withdrawtype"] = $info["withdrawtype"]?explode(",",$info["withdrawtype"]):array();
		switch ($settype) {
			case 1:
				if($todo=="setting"){
    				$token = getaccess_token();
    				$url="https://api.weixin.qq.com/cgi-bin/wxopen/template/add?access_token=".$token;
				    if($tid=="tpl_wd_arrival"){//提现到账模
				        $data['id']='AT0830';
				        $data['keyword_id_list']=[5,3,1,8,6];
				    }elseif($tid=="tpl_wd_fail"){//提现失败
				        $data['id']='AT1242';
				        $data['keyword_id_list']=[3,2];
				    }elseif($tid=="tpl_share_check"){//分销审核
				        $data['id']='AT0674';
				        $data['keyword_id_list']=[2,4,1];
				    }else{
				        message('设置失败,参数错误','','error');
				    }
				    $data=json_encode($data);
				    $info = tocurl($url,$data,0);
				    $add=json_decode($info,true);

				    if($add['errcode']==0&&$add['errmsg']=='ok'){
				        if(empty($_GPC['id'])){
				            $datas['uniacid']=trim($_W['uniacid']);
				            $datas[$tid] = $add['template_id'];
				            $res=pdo_insert('ymmf_sun_distribution_set',$datas);
				        }else{
				            $res=pdo_update('ymmf_sun_distribution_set',array($tid=>$add['template_id']),array('uniacid'=>$_W['uniacid']));
				        }
				        if($res){
				            message('模板消息设置成功',$this->createWebUrl('distribution_set', array('settype'=>1)),'success');
				        }else{
				            message('设置失败!','','error');
				        }
				    }else{
				        message('设置失败!!'.$add['errmsg'],'','error');
				    }

				}elseif($todo=="savetemplate"){
					$data = array();
					$data_first = $_GPC["indata"];
					$data = $data_first;
					if (empty($_GPC['id'])) {
						$data["uniacid"] = $_W['uniacid'];
						$res = pdo_insert('ymmf_sun_distribution_set', $data);
					}else{
						$res = pdo_update('ymmf_sun_distribution_set', $data, array('uniacid' => $_W['uniacid']));
					}
					if($res){
						message('成功!', $this->createWebUrl('distribution_set', array('settype'=>1)), 'success');
					}else{
						message('失败!', $this->createWebUrl('distribution_set', array('settype'=>1)), 'error');
					}
				}
				break;
			default:
				if (checksubmit()) {
					$data = array();
					$data_first = $_GPC["indata"];
					$urlarr = array();
					if($settype==0){
						$data_first["withdrawtype"] = implode(",",$data_first["withdrawtype"]);
					}else{
						$urlarr["settype"] = $settype;
					}
					$data = $data_first;
					if (empty($_GPC['id'])) {
						$data["uniacid"] = $_W['uniacid'];
						$res = pdo_insert('ymmf_sun_distribution_set', $data);
					}else{
						$res = pdo_update('ymmf_sun_distribution_set', $data, array('uniacid' => $_W['uniacid']));
					}
					if($res){
						message('成功!', $this->createWebUrl('distribution_set',$urlarr), 'success');
					}else{
						message('失败!', $this->createWebUrl('distribution_set',$urlarr), 'error');
					}
				}
				break;
		}
		include $this->template('web/distribution_set');
	}

	//设置佣金
	public function doWebdistribution_commission() {
		global $_GPC, $_W;
		$info = pdo_get('ymmf_sun_distribution_set', array('uniacid' => $_W['uniacid']));
		if (checksubmit()) {
			$data = array();
			$data_first = $_GPC["indata"];
			$data = $data_first;
			if (empty($_GPC['id'])) {
				$data["uniacid"] = $_W['uniacid'];
				$res = pdo_insert('ymmf_sun_distribution_set', $data);
			}else{
				$res = pdo_update('ymmf_sun_distribution_set', $data, array('uniacid' => $_W['uniacid']));
			}
			if($res){
				message('成功!', $this->createWebUrl('distribution_commission'), 'success');
			}else{
				message('失败!', $this->createWebUrl('distribution_commission'), 'error');
			}

		}
		include $this->template('web/distribution_commission');
	}

	//提现列表
	public function doWebdistribution_withdraw() {
		global $_GPC, $_W;
		$wd_type = array("","微信昵称","支付宝账号","银行卡号","用户名");
		$status = array("待打款","已经打款","驳回");
		$status_color = array("","blue","red");
		$wd_type_str = array("","微信","支付宝","银行卡","余额");
		$pageindex = max(1, intval($_GPC['page']));
		$pagesize=10;
		$uniacid = $_W['uniacid'];
		$where = " where w.uniacid=:uniacid ";
		$data[':uniacid']=$uniacid;
		$sql = "select w.*,u.name,u.img from ".tablename('ymmf_sun_distribution_withdraw')." as w left join ".tablename('ymmf_sun_user')." as u on u.openid=w.openid ".$where;
		$select_sql =$sql." order by id desc LIMIT " .($pageindex - 1) * $pagesize.",".$pagesize;
		// echo $select_sql;exit;
		$list=pdo_fetchall($select_sql,$data);
		$total=pdo_fetchcolumn("select count(*) as wname from ".tablename('ymmf_sun_distribution_withdraw')." as w left join ".tablename('ymmf_sun_user')." as u on u.openid=w.openid ".$where,$data);
		$pager = pagination($total, $pageindex, $pagesize);

		include $this->template('web/distribution_withdraw');
	}

	//改变提现状态——其他小程序部分内容需要修改
	public function doWebChangeWithdraw(){
		global $_GPC, $_W;
		$uniacid = $_W['uniacid'];
		$id = intval($_GPC["id"]);
		$status = intval($_GPC["status"]);
		$info = pdo_get('ymmf_sun_distribution_withdraw', array('uniacid' => $_W['uniacid'],'id' => $id));
		$wd_type = array("","微信零钱","支付宝","银行卡","账户余额");
		$sendtplmessage = false;
		if($status==1){
			if($info["withdrawaltype"]==1){//微信打款
				include IA_ROOT . '/addons/ymmf_sun_plugin_distribution/wxfirmpay.php';
	            $appData = pdo_get('ymmf_sun_system', array('uniacid' => $uniacid));
	            $mch_appid = $appData['appid'];
	            $mchid = $appData['mchid'];
	            $key = $appData['wxkey'];
	            $openid = $info["openid"];
	            $partner_trade_no = $mchid.time().rand(100000,999999);
	            $re_user_name = $info["uname"];
	            $desc = "分销提现打款";
	            $amount = $info["realmoney"]*100;
	            $apiclient_cert = IA_ROOT . "/addons/ymmf_sun/cert/".$appData['apiclient_cert'];
	            $apiclient_key = IA_ROOT . "/addons/ymmf_sun/cert/".$appData['apiclient_key'];

	            if($appData["appid"]=='' || $appData["mchid"]=='' || $appData["wxkey"]=='' || $appData["apiclient_cert"]=='' || $appData["apiclient_key"]==''){
	                message('失败，请先配置好小程序!', $this->createWebUrl('distribution_withdraw'), 'error');
	            }

	            $weixinfirmpay = new WeixinfirmPay($mch_appid, $mchid, $key, $openid,$partner_trade_no,$re_user_name,$desc,$amount,$apiclient_cert,$apiclient_key);
	            $return = $weixinfirmpay->pay();


	            if($return['result_code']=='SUCCESS'){
	            	//扣除冻结的金额,和可提现金额
	            	$res1 = pdo_update('ymmf_sun_distribution_promoter', array("freezemoney -="=>$info["money"],"canwithdraw -="=>$info["money"],"allratesmoney +="=>$info["ratesmoney"]), array('uniacid' => $_W['uniacid'],'uid' => $info["uid"]));
	            	//更新为已经打款
			        $res2 = pdo_update('ymmf_sun_distribution_withdraw', array("status"=>1), array('uniacid' => $_W['uniacid'],'id' => $id));
			        //发送模板消息
			        $sendtplmessage = true;
	            }else{
                	message('失败，错误信息：'.$return['return_msg'], $this->createWebUrl('distribution_withdraw'), 'error');
            	}
			}elseif($info["withdrawaltype"]==4){//余额
				//更新用户余额
				$res = pdo_update('ymmf_sun_user', array("money +="=>$info["realmoney"]), array('uniacid' => $_W['uniacid'],'id' => $info["uid"]));
				if($res){
					//扣除冻结的金额,和可提现金额
	            	$res1 = pdo_update('ymmf_sun_distribution_promoter', array("freezemoney -="=>$info["money"],"canwithdraw -="=>$info["money"],"allratesmoney +="=>$info["ratesmoney"]), array('uniacid' => $_W['uniacid'],'uid' => $info["uid"]));
	            	$data = array();
	            	$data["openid"] = $info["openid"];
		            $data["rid"] = 0;
		            $data["r_money"] = $info["realmoney"];
		            $data["r_dtime"] = time();
		            $data["uniacid"] = $uniacid;
		            $res3=pdo_insert('ymmf_sun_recharges',$data);
	            	//更新为已经打款
			        $res2 = pdo_update('ymmf_sun_distribution_withdraw', array("status"=>1), array('uniacid' => $_W['uniacid'],'id' => $id));
			        //发送模板消息
			        $sendtplmessage = true;
				}else{
					message('失败，错误信息：参数错误', $this->createWebUrl('distribution_withdraw'), 'error');
				}
			}else{
				//扣除冻结的金额,和可提现金额
	            $res1 = pdo_update('ymmf_sun_distribution_promoter', array("freezemoney -="=>$info["money"],"canwithdraw -="=>$info["money"],"allratesmoney +="=>$info["ratesmoney"]), array('uniacid' => $_W['uniacid'],'uid' => $info["uid"]));
				//更新为已经打款
			    $res2 = pdo_update('ymmf_sun_distribution_withdraw', array("status"=>1), array('uniacid' => $_W['uniacid'],'id' => $id));
			    //发送模板消息
			    $sendtplmessage = true;
			}
			if($sendtplmessage){
				//判断要发送的formid是否大于7天
				$sevenday = strtotime('-7 days');
				if($info["time"]>=$sevenday){
					$haveformid = $info["form_id"];
				}
				$tpldata = array();
				$tpldata["price"] = $info["money"];
				$tpldata["ratesmoney"] = $info["ratesmoney"];
				$tpldata["realmoney"] = $info["realmoney"];
				$tpldata["wd_type"] = $wd_type[$info["withdrawaltype"]];
				sendtelmessage($info["openid"],"tpl_wd_arrival",$haveformid,$tpldata);
			}
		}else{
			$data = array();
	        $data["status"] = $status;
	        $res = pdo_update('ymmf_sun_distribution_withdraw', $data, array('uniacid' => $_W['uniacid'],'id' => $id));
	        if($status==2){//提现被拒绝
				//拒绝修改冻结的金额,和可提现金额
	            $res1 = pdo_update('ymmf_sun_distribution_promoter', array("freezemoney -="=>$info["money"]), array('uniacid' => $_W['uniacid'],'uid' => $info["uid"]));

	        	//判断要发送的formid是否大于7天
				$sevenday = strtotime('-7 days');
				if($info["time"]>=$sevenday){
					$haveformid = $info["form_id"];
				}
				$tpldata = array();
				$tpldata["price"] = $info["money"];
				sendtelmessage($info["openid"],"tpl_wd_fail",$haveformid,$tpldata);
	        }
		}
		
		message('成功!', $this->createWebUrl('distribution_withdraw'), 'success');
	}

	//分销商
	public function doWebdistributor() {
		global $_GPC, $_W;
		$wd_type = array("","微信昵称","支付宝账号","银行卡号","用户名");
		$status = array("待审核","通过审核","拒绝");
		$pageindex = max(1, intval($_GPC['page']));
		$pagesize=10;
		$uniacid = $_W['uniacid'];
		$where = " where w.uniacid=:uniacid ";
		$data[':uniacid']=$uniacid;
		$sql = "select w.*,u.name as uname,u.img from ".tablename('ymmf_sun_distribution_promoter')." as w left join ".tablename('ymmf_sun_user')." as u on u.openid=w.openid ".$where;
		$select_sql =$sql." order by w.id desc LIMIT " .($pageindex - 1) * $pagesize.",".$pagesize;
		$list=pdo_fetchall($select_sql,$data);
		//获取下级
		$info = pdo_get('ymmf_sun_distribution_set', array('uniacid' => $_W['uniacid']));
		if($info["status"]>0){
			foreach($list as $key => $val){
				$list[$key]["code_img"] = "";
				if($info["status"]>=1){//获取一级
					$list[$key]["nextone"]=pdo_fetchcolumn("select count(id) as wname from ".tablename('ymmf_sun_user')." where parents_id=".$val["uid"]." ");
				}
				if($info["status"]>=2){//获取二级
					$list[$key]["nexttwo"]=pdo_fetchcolumn("select count(id) as wname from ".tablename('ymmf_sun_user')." where parents_id=ANY(SELECT id as user1 FROM ".tablename('ymmf_sun_user')." WHERE parents_id=".$val["uid"]." and uniacid=".$uniacid.") and uniacid=".$uniacid."; ");
				}
				if($info["status"]>=3){//获取三级
					$list[$key]["nextthree"]=pdo_fetchcolumn("select count(id) as wname from ".tablename('ymmf_sun_user')." where parents_id=ANY(SELECT id as user1 FROM ".tablename('ymmf_sun_user')." WHERE parents_id=ANY(SELECT id as user1 FROM ".tablename('ymmf_sun_user')." WHERE parents_id=".$val["uid"]." and uniacid=".$uniacid.") and uniacid=".$uniacid.") and uniacid=".$uniacid."; ");
				}
			}
		}
		// print_r($list);
		$total=pdo_fetchcolumn("select count(*) as wname from ".tablename('ymmf_sun_distribution_promoter')." as w left join ".tablename('ymmf_sun_user')." as u on u.openid=w.openid ".$where,$data);
		$pager = pagination($total, $pageindex, $pagesize);

		include $this->template('web/distributor');
	}

	//分销订单列表
	public function doWebdistribution_order() {
		global $_GPC, $_W;
		$uid = intval($_GPC["uid"]);
		$ordertype = intval($_GPC["ordertype"]);
		$ordertype = $ordertype>0?$ordertype:1;
		$pageindex = max(1, intval($_GPC['page']));
		$pagesize=10;
		$uniacid = $_W['uniacid'];
		
		//$state = array("","取消订单","待付款","已付款","待收货","已完成");
		//$status = array("","待付款","已付款","待服务","已完成","退款中","已退款","退款拒绝");
		$status = array("","","待付款","已付款","待服务","已完成");

		$where = " where o.uniacid='".$uniacid."' and os.ordertype='".$ordertype."' ";
		if($uid>0){
			$where .= " AND (os.parent_id_1='".$uid."' or os.parent_id_2='".$uid."' or os.parent_id_3='".$uid."') AND os.parent_id_1>0 ";
		}else{
			$where .= " AND os.parent_id_1>0 ";
		}
		if($ordertype==1){
			$sql = 'select o.state,o.isdefault,u.name,u.img as uthumb,o.order_num,o.good_num,o.money,o.id,o.pname,h.logo,os.first_price,os.second_price,os.third_price from '.tablename('ymmf_sun_distribution_order').' as os left join '.tablename('ymmf_sun_order').' as o on o.id=os.order_id left join 
			'.tablename('ymmf_sun_user').' as u on u.openid=os.openid left join '.tablename('ymmf_sun_hairers').' as h on h.id=o.hair_id '.$where.' order by o.id desc ';
            
			$total=pdo_fetchcolumn('select count(*) as wname from '.tablename('ymmf_sun_distribution_order').' as os left join '.tablename('ymmf_sun_order').' as o on o.id=os.order_id left join '.tablename('ymmf_sun_user').' as u on u.openid=os.openid '.$where.' order by o.id desc ');
        }elseif($ordertype==2){
            $sql = 'select o.status,u.name,u.img as uthumb,o.orderNum as order_num,ol.num as good_num,o.money,o.id,b.gname as pname,b.pic as logo,os.first_price,os.second_price,os.third_price from '.tablename('ymmf_sun_distribution_order').' as os left join '.tablename('ymmf_sun_kjorder').' as o on o.id=os.order_id left join 
			'.tablename('ymmf_sun_user').' as u on u.openid=os.openid left join '.tablename('ymmf_sun_kjorderlist').' as ol on ol.order_id=o.id left join '.tablename('ymmf_sun_new_bargain').' as b on b.id=ol.oid'.$where.' order by o.id desc ';
            
			$total=pdo_fetchcolumn('select count(*) as wname from '.tablename('ymmf_sun_distribution_order').' as os left join '.tablename('ymmf_sun_kjorder').' as o on o.id=os.order_id left join '.tablename('ymmf_sun_user').' as u on u.openid=os.openid '.$where.' order by o.id desc ');
        }

		$select_sql =$sql." LIMIT " .($pageindex - 1) * $pagesize.",".$pagesize;
		//echo $select_sql;
		$list=pdo_fetchall($select_sql,$data);
		
		foreach($list as $key=>$value){
			if($list[$key]['isdefault']==1){
				$list[$key]['state']=5;
			}elseif($list[$key]['isdefault']==0){
				$list[$key]['state']=3;
			}

			if($list[$key]['status']==2){
				$list[$key]['state']=2;
			}elseif($list[$key]['status']==3){
				$list[$key]['state']=3;
			}elseif($list[$key]['status']==4){
				$list[$key]['state']=4;
			}elseif($list[$key]['status']==5){
				$list[$key]['state']=5;
			}
		}
		$pager = pagination($total, $pageindex, $pagesize);

		$d_set = pdo_get('ymmf_sun_distribution_set', array('uniacid' => $_W['uniacid']));

		include $this->template('web/distribution_order');
	}

	//改变状态
    public function doWebSetStatus(){
        global $_GPC, $_W;
        $uniacid = $_W['uniacid'];
        $status = intval($_GPC["status"]);
        $id = intval($_GPC["id"]);
        $data = array();
        $data["status"] = $status;
        $res = pdo_update('ymmf_sun_distribution_promoter', $data, array('uniacid' => $_W['uniacid'],'id' => $id));

        if($res){
        	//发送模板消息
			$ress = pdo_get('ymmf_sun_distribution_promoter', array('uniacid' => $_W['uniacid'],'id' => $id));
    		$status = array("审核中","已通过","被拒绝");
        	//判断要发送的formid是否大于7天
			$sevenday = strtotime('-7 days');
			if($ress["addtime"]>=$sevenday){
				$haveformid = $ress["form_id"];
			}
			$tpldata = array();
			$tpldata["status"] = $status[$ress["status"]];
			sendtelmessage($ress["openid"],"tpl_share_check",$haveformid,$tpldata);


			message('成功!', $this->createWebUrl('distributor'), 'success');
		}else{
			message('失败!', $this->createWebUrl('distributor'), 'error');
		}
    } 

    //删除
    public function doWebDeleteDistributor(){
    	global $_GPC, $_W;
    	$uniacid = $_W['uniacid'];
    	$id = intval($_GPC["id"]);
    	$delres=pdo_delete('ymmf_sun_distribution_promoter',array('uniacid'=>$uniacid,'id'=>$id));
    	if($delres){
    		message('成功!', $this->createWebUrl('distributor'), 'success');
    	}else{
    		message('失败!', $this->createWebUrl('distributor'), 'error');
    	}
    }


}