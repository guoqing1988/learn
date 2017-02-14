<?php
header("Content-Type: text/html; charset=UTF-8");

include("/data/www/platform/spirit/web2_fast/kernel/SphinxApi.php");
class Search {
    public $cl; //搜索句柄
    public $mode = SPH_MATCH_EXTENDED2;
    /*
    SPH_MATCH_ALL, 匹配所有查询词（默认模式）
 SPH_MATCH_ANY, 匹配查询词中的任意一个
 SPH_MATCH_PHRASE, 将整个查询看作一个词组，要求按顺序完整匹配
 SPH_MATCH_BOOLEAN, 将查询看作一个布尔表达式 （参见 节 4.2, “ 布尔查询语
法 ”
 SPH_MATCH_EXTENDED, 将查询看作一个Sphinx内部查询语言的表达式（参见
节 4.3, “ 扩展的查询语法 ” ）
    */
	public $fields="certificate_id,goods_id,cat_id,type_id,brand_id";
    public $index = "goods incremental_goods";
    private $groupby = "certificate_id";
    //private $groupsort = "@count desc";
    public $sortby = "last_modify DESC";
    private $certificate_id = 0;
    private $start_time = 0;
    private $end_time = 0;
    private $cat_id = 0;
    private $goods_id = 0;
    private $disabled = 0;
    private $marketable = 0;
    //private $bn = "";
    //private $name = "";
    private $limit = 20;
    private $offset = false;
    private $page = 1;
    private $res = false;
    
     /**
     * Command
     */
    function Search($host,$port){
        $this->cl = new SphinxClient();
        $this->cl->SetServer( $host, (int)$port );
        $this->cl->SetConnectTimeout( 1 );
    }
    
    /**
     * setPages...
     *
     * @param int $pages
     */
    function setPages($pages){
        $this->page = $pages;
    }
    
    /**
     * setPages...
     *
     * @param int $pages
     */
    function setOffset($offset){
        $this->offset = $offset;
    }
    
    /**
     * setLimit...
     *
     * @param int $limit
     */
    
    function setLimit($limit){
       $this->limit = $limit;
    }
    
    /**
     * setSupplierID...
     *
     * @param int $supplier_id
     */
    
    function setCertificateId($certificate_id){
       if(is_array($certificate_id)){
          $this->certificate_id = $certificate_id;
       }else{
          $this->certificate_id = array($certificate_id);
       }
    }
    
    /**
     * setStartTime...
     *
     * @param int $start_time
     */
    function setStartTime($start_time){
       $this->start_time = $start_time;
      }
    
      /**
       * setEndTime...
       *
       * @param int $end_time
       */
    function setEndTime($end_time){
       $this->end_time = $end_time;
    }
    
    /**
     * setCatID...
     *
     * @param int $cat_id
     */
    
    function setCatID($cat_id){
       if(is_array($cat_id)){
          $this->cat_id = $cat_id;
       }else{
          $this->cat_id = array($cat_id);
       }
    }

    
    /**
     * setMarkeTable...
     *
     * @param int $marketable
     */
    
    function setMarkeTable($marketable){
       if(is_array($marketable)){
          $this->marketable = $marketable;
       }else{
          $this->marketable = array($marketable);
       }
    }
    
    /**
     * setMarkeTable...
     *
     * @param int $marketable
     */
    
    function setDisabled($disabled){
       if(is_array($disabled)){
          $this->disabled = $disabled;
       }else{
          $this->disabled = array($disabled);
       }
    }
    

    
    /**
     * query...
     *
     */
    
    function query($searchname=''){
        if($this->certificate_id){
            $this->cl->SetFilter("certificate_id",$this->certificate_id);
        }
    	if( $this->start_time ){
    		!$this->end_time and $this->end_time=time();
    		$this->cl->SetFilterRange('last_modify',$this->start_time,$this->end_time);
    	}        
        if($this->cat_id){
            $this->cl->SetFilter("cat_id",$this->cat_id);
        }
        if($this->disabled){
            $this->cl->SetFilter("disabled",$this->disabled);
        }
    	if($this->marketable){
            $this->cl->SetFilter("marketable",$this->marketable);
        }	
        if($this->bn){
            $this->cl->SetFilter("bn",array($this->bn));
        }        
        if($this->gbn){
            $this->cl->SetFilter("gbn",array($this->gbn));
        }
        if($this->name){
            $this->cl->SetFilter("goods_name",$this->name);
        }
        if($this->goods_id){
            $this->cl->SetFilter("goods_id",$this->goods_id);
        }
        $this->cl->SetMatchMode($this->mode);
        $this->cl->SetSortMode( SPH_MATCH_EXTENDED, $this->sortby );
        $this->cl->SetArrayResult( true );
        
        if( $this->limit ){
            if($this->offset !== false ){
                $this->cl->SetLimits( $this->offset,intval($this->limit),100000 );
            }else {
                $this->cl->SetLimits( ($this->page - 1)*intval($this->limit),intval($this->limit),100000 );
            }
        }
		$this->cl->SetSelect($this->fields);
        $this->res = $this->cl->Query( $searchname, $this->index );
       // print_r($this->res);exit;
    }


    function getCount(){
        if($this->res === false){
            return 0;
        }else{
            return $this->res['total'];
        }
    }
    
    /**
     * Enter getResult...
     *
     * @return array
     */

    function getResult(){
        $data = array();
        if($this->res === false){
            return $this->cl->GetLastError();
        }else{
            $data['total']       = $this->res['total'];
            $data['total_found'] = $this->res['total_found'];
            $data['time']        = $this->res['time'];
            if(isset($this->res["words"]) && is_array($this->res["words"])){
                $data['words']       = $this->res["words"];
            }
            $data['data'] = array();
            if(isset($this->res["matches"]) && is_array($this->res["matches"])){
                foreach($this->res["matches"] as $docinfo){
                    $info = array();
                    // $info['id'] = $docinfo['id'];
                    // $info['weight'] = $docinfo['weight'];
                    foreach ( $docinfo["attrs"] as $attrname => $attrtype ){
                        $info[$attrname] = $attrtype;
                    }
                    $data['data'][] = $info;
                }
            }
        }
        return $data;
    }
}

$obj_search = new Search('192.168.75.242','3315');
// $obj_search->name='123';
// $obj_search->bn='213213213';
// $obj_search->cl->SetSelect('*');
// $obj_search->setCatID('1');
$obj_search->setCertificateId('123');
$obj_search->query("");
print_r($obj_search->getResult());

