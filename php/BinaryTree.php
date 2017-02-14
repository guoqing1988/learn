<?php
/**
 * 二叉树的定义
 */
class BinaryTree {
    protected $key = NULL;      //  当前节点的值
    protected $left = NULL;     //  左子树
    protected $right = NULL;    //  右子树
 
    /**
     * 以指定的值构造二叉树，并指定左右子树
     *
     * @param mixed $key 节点的值.
     * @param mixed $left 左子树节点.
     * @param mixed $right 右子树节点.
     */
    public function __construct( $key = NULL, $left = NULL, $right = NULL) {
        $this->key = $key;
        if ($key === NULL) {
            $this->left = NULL;
            $this->right = NULL;
        }
        elseif ($left === NULL) {
            $this->left = new BinaryTree();
            $this->right = new BinaryTree();
        }
        else {
            $this->left = $left;
            $this->right = $right;
        }
    }
 
    /**
     * 析构方法.
     */
    public function __destruct() {
        $this->key = NULL;
        $this->left = NULL;
        $this->right = NULL;
    }
 
    /**
    * 清空二叉树.
    **/
    public function purge () {
        $this->key = NULL;
        $this->left = NULL;
        $this->right = NULL;
    }
 
    /**
     * 测试当前节点是否是叶节点.
     *
     * @return boolean 如果节点非空并且有两个空的子树时为真，否则为假.
     */
    public function isLeaf() {
        return !$this->isEmpty() &&
            $this->left->isEmpty() &&
            $this->right->isEmpty();
    }
 
    /**
     * 测试节点是否为空
     *
     * @return boolean 如果节点为空返回真，否则为假.
     */
    public function isEmpty() {
        return $this->key === NULL;
    }
 
    /**
     * Key getter.
     *
     * @return mixed 节点的值.
     */
    public function getKey() {
        if ($this->isEmpty()) {
            return false;
        }
        return $this->key;
    }
 
 
    /**
     * 给节点指定Key值,节点必须为空
     *
     * @param mixed $object 添加的Key值.
     */
    public function attachKey($obj) {
        if (!$this->isEmpty())
            return false;
        $this->key = $obj;
        $this->left = new BinaryTree();
        $this->right = new BinaryTree();
    }
 
    /**
     * 删除key值，使得节点为空.
     */
    public function detachKey() {
        if (!$this->isLeaf())
            return false;
        $result = $this->key;
        $this->key = NULL;
        $this->left = NULL;
        $this->right = NULL;
        return $result;
    }
 
    /**
     * 返回左子树
     *
     * @return object BinaryTree 当前节点的左子树.
     */
    public function getLeft() {
        if ($this->isEmpty())
            return false;
        return $this->left;
    }
 
    /**
     * 给当前结点添加左子树
     *
     * @param object BinaryTree $t 给当前节点添加的子树.
     */
    public function attachLeft(BinaryTree $t) {
        if ($this->isEmpty() || !$this->left->isEmpty())
            return false;
        $this->left = $t;
    }
 
    /**
     * 删除左子树
     *
     * @return object BinaryTree  返回删除的左子树.
     */
    public function detachLeft() {
        if ($this->isEmpty())
            return false;
        $result = $this->left;
        $this->left = new BinaryTree();
        return $result;
    }
 
    /**
     * 返回当前节点的右子树
     *
     * @return object BinaryTree 当前节点的右子树.
     */
    public function getRight() {
        if ($this->isEmpty())
            return false;
        return $this->right;
    }
 
    /**
     * 给当前节点添加右子树
     *
     * @param object BinaryTree $t 需要添加的右子树.
     */
    public function attachRight(BinaryTree $t) {
        if ($this->isEmpty() || !$this->right->isEmpty())
            return false;
        $this->right = $t;
    }
 
    /**
     * 删除右子树，并返回此右子树
     * @return object BinaryTree 删除的右子树.
     */
    public function detachRight() {
        if ($this->isEmpty ())
            return false;
        $result = $this->right;
        $this->right = new BinaryTree();
        return $result;
    }
 
    /**
     * 先序遍历
     */
    public function preorderTraversal() {
        if ($this->isEmpty()) {
            return ;
        }
        echo ' ', $this->getKey();
        $this->getLeft()->preorderTraversal();
        $this->getRight()->preorderTraversal();
    }
 
    /**
     * 中序遍历
     */
    public function inorderTraversal() {
        if ($this->isEmpty()) {
            return ;
        }
        $this->getLeft()->preorderTraversal();
        echo ' ', $this->getKey();
        $this->getRight()->preorderTraversal();
    }
 
    /**
     * 后序遍历
     */
    public function postorderTraversal() {
        if ($this->isEmpty()) {
            return ;
        }
        $this->getLeft()->preorderTraversal();
        $this->getRight()->preorderTraversal();
        echo ' ', $this->getKey();
    }
}
 
/**
 * 二叉排序树的PHP实现
 */
 
class BST extends BinaryTree {
  /**
     * 构造空的二叉排序树
     */
    public function __construct() {
        parent::__construct(NULL, NULL, NULL);
    }
 
    /**
     * 析构
     */
    public function __destruct() {
        parent::__destruct();
    }
 
    /**
     * 测试二叉排序树中是否包含参数所指定的值
     *
     * @param mixed $obj 查找的值.
     * @return boolean True 如果存在于二叉排序树中则返回真，否则为假期
     */
    public function contains($obj) {
        if ($this->isEmpty())
            return false;
        $diff = $this->compare($obj);
        if ($diff == 0) {
            return true;
        }elseif ($diff < 0)
            return $this->getLeft()->contains($obj);
        else
            return $this->getRight()->contains($obj);
    }
 
    /**
     * 查找二叉排序树中参数所指定的值的位置
     *
     * @param mixed $obj 查找的值.
     * @return boolean True 如果存在则返回包含此值的对象，否则为NULL
     */
    public function find($obj) {
        if ($this->isEmpty())
            return NULL;
        $diff = $this->compare($obj);
        if ($diff == 0)
            return $this->getKey();
        elseif ($diff < 0)
            return $this->getLeft()->find($obj);
        else
            return $this->getRight()->find($obj);
    }
 
    /**
     * 返回二叉排序树中的最小值
     * @return mixed 如果存在则返回最小值，否则返回NULL
     */
    public function findMin() {
        if ($this->isEmpty ())
            return NULL;
        elseif ($this->getLeft()->isEmpty())
            return $this->getKey();
        else
            return $this->getLeft()->findMin();
    }
 
    /**
     * 返回二叉排序树中的最大值
     * @return mixed 如果存在则返回最大值，否则返回NULL
     */
    public function findMax() {
        if ($this->isEmpty ())
            return NULL;
        elseif ($this->getRight()->isEmpty())
            return $this->getKey();
        else
            return $this->getRight()->findMax();
    }
 
    /**
     * 给二叉排序树插入指定值
     *
     * @param mixed $obj 需要插入的值.
     * 如果指定的值在树中存在，则返回错误
     */
    public function insert($obj) {
        var_dump($this->getKey());
        if ($this->isEmpty()) {
            $this->attachKey($obj);
        } else {
            
            $diff = $this->compare($obj);
            if ($diff == 0)
                die('argu error');
            if ($diff < 0)
                $this->getLeft()->insert($obj);
            else
                $this->getRight()->insert($obj);
        }
        $this->balance();
    }
 
 
     /**
     * 从二叉排序树中删除指定的值
     *
     * @param mixed $obj 需要删除的值.
     */
    public function delete($obj) {
        if ($this->isEmpty ())
            die();
 
        $diff = $this->compare($obj);
        if ($diff == 0) {
            if (!$this->getLeft()->isEmpty()) {
                $max = $this->getLeft()->findMax();
                $this->key = $max;
                $this->getLeft()->delete($max);
            }
            elseif (!$this->getRight()->isEmpty()) {
                $min = $this->getRight()->findMin();
                $this->key = $min;
                $this->getRight()->delete($min);
            } else
                $this->detachKey();
        } else if ($diff < 0)
                $this->getLeft()->delete($obj);
            else
                $this->getRight()->delete($obj);
        $this->balance();
    }
 
    public function compare($obj) {
        return $obj - $this->getKey();
    }
 
    /**
     * Attaches the specified object as the key of this node.
     * The node must be initially empty.
     *
     * @param object IObject $obj The key to attach.
     * @exception IllegalOperationException If this node is not empty.
     */
    public function attachKey($obj) {
        if (!$this->isEmpty())
            return false;
        $this->key = $obj;
        $this->left = new BST();
        $this->right = new BST();
    }
 
    /**
     * Balances this node.
     * Does nothing in this class.
     */
    protected function balance () {}
 
 
    /**
     * Main program.
     *
     * @param array $args Command-line arguments.
     * @return integer Zero on success; non-zero on failure.
     */
    public static function main($args) {
        printf("BinarySearchTree main program.\n");
        $root = new BST();
        foreach ($args as $row) {
            $root->insert($row);
        }
        return $root;
    }

    //前序遍历，访问根节点->遍历子左树->遍历右左树
    function preorder($root){
        $stack = array();
        array_push($stack, $root);
        while(!empty($stack)){
            $center_node = array_pop($stack);
            echo $center_node->key.' ';
            
            if($center_node->right != null) array_push($stack, $center_node->right);
            if($center_node->left != null) array_push($stack, $center_node->left);
        }
    }
    //中序遍历，遍历子左树->访问根节点->遍历右右树
    function inorder($root){
        $stack = array();
        $center_node = $root;
        while (!empty($stack) || $center_node != null) {
             while ($center_node != null) {
                 array_push($stack, $center_node);
                 $center_node = $center_node->left;
             }
             $center_node = array_pop($stack);
             echo $center_node->key . " ";

             $center_node = $center_node->right;
         }
    }
    //后序遍历，遍历子左树->访问子右树->遍历根节点
    function postorder($root){
        $pushstack = array();
        $visitstack = array();
        array_push($pushstack, $root);
        while(!empty($pushstack)) {
            $center_node = array_pop($pushstack);
            array_push($visitstack, $center_node);
            if ($center_node->left != null) array_push($pushstack, $center_node->left);
            if ($center_node->right != null) array_push($pushstack, $center_node->right);
        }
        while (!empty($visitstack)) {
            $center_node = array_pop($visitstack);
            echo $center_node->key. " ";
        }
    }
}
 
$root = BST::main(array(50, 3, 10, 5, 100, 56, 78,111,220,137,765,1));
print_r($root);//exit;
$root->preorder($root);
echo "\n";
$root->inorder($root);
echo "\n";
$root->postorder($root);
echo "\n";
$root->preorderTraversal();
echo "\n";
$root->inorderTraversal();
echo "\n";
$root->postorderTraversal();
echo "\n";
// echo $root->findMax();
// $root->delete(100);
// echo $root->findMax();