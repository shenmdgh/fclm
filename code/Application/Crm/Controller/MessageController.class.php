<?php
namespace Crm\Controller;
use Think\Controller;
use Crm\Model\AMessageModel;

class MessageController extends Controller {
	
	protected $instance = NULL;
	
	public function __construct()
	{
		parent::__construct();
		if($this->instance == NULL) {
			$this->instance = new AMessageModel;
		}		
	}
	/**
		func: 消息列表
		params: null
	*/
    public function index()
	{	
		$index = [
					"where" => "isdelete=0 AND agentid is null",
					"order" => "createtime DESC",
					"limit"	=> array(
							"firstRow" => $Page->firstRow,
							"listRows" => $Page->listRows,
						),
					];
    	$count      = $this->instance->where($index['where'])->count();	
		$Page       = new \Think\Page($count,5);
		$Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><span> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页 </span></li></ul>");
		$Pageshow       = $Page->show();		
		$message = $this->instance->index($index);
		$this->assign('message',$message);
		$this->assign('Pageshow',$Pageshow);
		$this->display();
	}
	
	/**
		func: 显示添加消息页
		params: null
	*/
	public function addPage()
	{
		return $this->display();
	}
	
	/**
		func: 添加消息
		params: null
	*/
	public function add()
	{
		$data["content"] = I('post.message');
		$data["level"]   = I('post.level');
		$data["levelmatch"] = I('post.levelmatch');			
		$data["senderid"] = session(C('USER_AUTH_KEY'));		
		$method = I('post.method');
		foreach($method as $value) {
			$data["sendtype"] += $value;
		}
		$status = $this->instance->addMessage($data);
		if($status) {
			return $this->success('添加成功', U('Crm/Message/index'));
		}			
		else{
			return $this->success('添加失败', U('Crm/Message/addPage'));
		}
	}
	
	/**
		func: 显示修改消息页
		params: null
	*/
	public function editPage()
	{
		$id = I("get.id", 0, 'intval');
		if(!$id) {
			return $this->success('显示失败', U('Crm/Message/index'));
		}
		$data["find"] = $id;		
		$message = $this->instance->showMessage($data);
		if( $message=== false) {
			return $this->success('显示失败', U('Crm/Message/index'));
		}
		$sendbin = sprintf("%03b", $message['sendtype']);
		$sendtype['web']   = $sendbin & sprintf("%03b", 1);
		$sendtype['email'] = $sendbin & sprintf("%03b", 2);
		$sendtype['sms']   = $sendbin & sprintf("%03b", 4);
		
		$this->assign('sendtype',$sendtype);
		$this->assign('message',$message);
		$this->display();	
	}
	
	/**
		func: 修改消息
		params: null
	*/
	public function edit()
	{
		$data["id"] = I('post.id');
		$data["content"] = I('post.message');
		$data["level"]   = I('post.level');
		$data["levelmatch"] = I('post.levelmatch');
		$method = I('post.method');
		foreach($method as $value) {
			$data["sendtype"] += $value;
		}
		$status = $this->instance->editMessage($data);
		if($status !== false) {
			return $this->success('修改成功', U('Crm/Message/index'));
		}			
		else{
			return $this->success('修改失败', U('Crm/Message/editPage', ['id'=>$data["id"]]));
		}
	}
	
	/**
		func: 发送消息
		params: null
	*/
	public function send()
	{
		$id = I("get.id", 0, 'intval');
		if(!$id) {
			return $this->success('发送失败', U('Crm/Message/index'));
		}		
		$data["find"] = $id;
		$status = $this->instance->sendMessage($data);
		if(!$status) {
			return $this->success('发送失败', U('Crm/Message/index'));
		}
		return $this->success('加入发送队列, 等待发送...', U('Crm/Message/index'));
	}
	
	/**
		func: 删除消息
		params: null
	*/
	public function delete()
	{
		$id = I("get.id", 0, 'intval');
		if(!$id) {
			return $this->success('删除失败', U('Crm/Message/index'));
		}
		$data["id"] = $id;
		$data["isdelete"] = 1;
		$status = $this->instance->deleteMessage($data);
		if($status !== false) {
			return $this->success('删除成功', U('Crm/Message/index'));
		}			
		else{
			return $this->success('删除失败', U('Crm/Message/index'));
		}
	}
}