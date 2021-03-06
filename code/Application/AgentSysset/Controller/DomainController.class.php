<?php
namespace AgentSysset\Controller;
use Think\Controller;
use AgentSysset\Model\ADomainModel;
use AgentSysset\Model\ADomainapplyModel;
class DomainController extends Controller {
    protected $instance = NULL;
    public function __construct()
    {
        parent::__construct();
        if($this->instance == NULL) {
            $this->instance = new ADomainModel;
        }
    }

    public function index()
    {

        $id = session(C('AGENT_USER_AUTH_DATA'))['id'];
        $count      = $this->instance->where("1=1 AND agentid = $id")->count();
        $Page       = new \Think\Page($count,5);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><span> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页 </span></li></ul>");
        $Pageshow       = $Page->show();
        $index = [
            "where" => "1=1 AND agentid = $id",
            "order" => "userdomain DESC",
            "limit"	=> array(
                "firstRow" => $Page->firstRow,
                "listRows" => $Page->listRows,
            ),
        ];
        $domains = $this->instance->index($index);
        $this->assign('domains',$domains);
        $this->assign('Pageshow',$Pageshow);
        $this->display();
    }
    public function apply()
    {
        $idf = session(C('AGENT_USER_AUTH_DATA'))['id'];
        $username = session(C('AGENT_USER_AUTH_DATA'))['username'];
        $userdomain = I('post.userdomain');
        if(!$userdomain)
        {
            return $this->error('申请域名不能为空！', U('AgentSysset/Domain/index'));
        }
        $data['userdomain'] = I('post.userdomain');
        $data['agentname'] = $username;
        $data['agentid'] = $idf;
        $data['userdomaincreatetime'] = strtotime(date('Y-m-d H:i:s', time()));
        $data['status'] = 0;
        $adddomain = new ADomainModel;
        $result = $adddomain->checkapply($idf);
        if($result)
        {
            return $this->success('你已经有申请记录！', U('AgentSysset/Domain/index'));
        }

        $resapply = $adddomain->adddomain($data);
        $addlog['createtime'] = strtotime(date('Y-m-d H:i:s', time()));
        $addlog['domain'] = I('post.userdomain');
        $addlog['agentid'] = $idf;
        $addlog['buserid'] = $idf;
        $addlogs = new ADomainapplyModel;
        $addlogs->addlog($addlog);
        if($resapply)
        {
            return $this->success('申请成功！', U('AgentSysset/Domain/index'));
        }
    }
    /*
     * 进入域名编辑页面
     */
    public function editdomain()
    {
        $this->display();
    }
    /*
     * 启用
     */
    public function enable()
    {
        $id = I('get.id');
        $data['status'] = 1;
        $domains = M('a_domain');
        $domains->where("id = $id")->save($data);
        if($domains)
        {
            return $this->success('启动成功！', U('AgentSysset/Domain/index'));
        }
    }
    /*
     * 禁用
     */
    public function disable()
    {

        $id = I('get.id');
        $data['status'] = 0;
        $domains = M('a_domain');
        $domains->where("id = $id")->save($data);
        if($domains)
        {
            return $this->success('禁用成功！', U('AgentSysset/Domain/index'));
        }

    }
    public function applydomain()
    {
        $this->display();
    }
    public function  delete()
    {
        $id = I('get.id');
        $domains = M('a_domain');
        $domains->where("id = $id")->delete();
        if($domains)
        {
            return $this->success('删除成功！', U('AgentSysset/Domain/index'));
        }
    }
}