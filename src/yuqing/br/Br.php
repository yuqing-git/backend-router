<?php
namespace br;
/**
 * 获取和设置配置参数 支持批量定义
 * 如果$key是关联型数组，则会按K-V的形式写入配置
 * 如果$key是数字索引数组，则返回对应的配置数组
 * @param string|array $key 配置变量
 * @param array|null $value 配置值
 * @return array|null
 */
function C($key,$value=null){
    static $_config = array();
    $args = func_num_args();
    if($args == 1){
        if(is_string($key)){  //如果传入的key是字符串
            return isset($_config[$key])?$_config[$key]:null;
        }
        if(is_array($key)){
            if(array_keys($key) !== range(0, count($key) - 1)){  //如果传入的key是关联数组
                $_config = array_merge($_config, $key);
            }else{
                $ret = array();
                foreach ($key as $k) {
                    $ret[$k] = isset($_config[$k])?$_config[$k]:null;
                }
                return $ret;
            }
        }
    }else{
        if(is_string($key)){
            $_config[$key] = $value;
        }else{
            halt('传入参数不正确');
        }
    }
    return null;
}
/**
 * 终止程序运行
 * @param string $str 终止原因
 * @param bool $display 是否显示调用栈，默认不显示
 * @return void
 */
function halt($str, $display=false){
    Log::fatal($str.' debug_backtrace:'.var_export(debug_backtrace(), true));
    header("Content-Type:text/html; charset=utf-8");
    if($display){
        echo "<pre>";
        debug_print_backtrace();
        echo "</pre>";
    }
    echo $str;
    exit;
}
/**
 * 如果文件存在就include进来
 * @param string $path 文件路径
 * @return void
 */
function includeIfExist($path){
    if(file_exists($path)){
        include $path;
    }
}
/**
 * 总控类
 */
class Br {
    /**
     * 控制器
     * @var string
     */
    private $c;
    /**
     * Action
     * @var string
     */
    private $a;
    /**
     * 单例
     * @var SinglePHP
     */
    private static $_instance;
    /**
     * 构造函数，初始化配置
     * @param array $conf
     */
    private function __construct($conf){
        C($conf);
    }
    private function __clone(){}
    /**
     * 获取单例
     * @param array $conf
     * @return SinglePHP
     */
    public static function getInstance($conf){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($conf);
        }
        return self::$_instance;
    }
    /**
     * 运行应用实例
     * @access public
     * @return void
     */
    public function run(){
        if(C('USE_SESSION') == true){
            session_start();
        }
        C('APP_FULL_PATH', getcwd().'/'.C('APP_PATH').'/');
        includeIfExist( C('APP_FULL_PATH').'/common.php');
        $pathMod = C('PATH_MOD');
        $pathMod = empty($pathMod)?'NORMAL':$pathMod;
        spl_autoload_register(array('SinglePHP', 'autoload'));
        if(strcmp(strtoupper($pathMod),'NORMAL') === 0 || !isset($_SERVER['PATH_INFO'])){
            $this->c = isset($_GET['c'])?$_GET['c']:'Index';
            $this->a = isset($_GET['a'])?$_GET['a']:'Index';
        }else{
            $pathInfo = isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'';
            $pathInfoArr = explode('/',trim($pathInfo,'/'));
            if(isset($pathInfoArr[0]) && $pathInfoArr[0] !== ''){
                $this->c = $pathInfoArr[0];
            }else{
                $this->c = 'Index';
            }
            if(isset($pathInfoArr[1])){
                $this->a = $pathInfoArr[1];
            }else{
                $this->a = 'Index';
            }
        }
        if(!class_exists($this->c.'Controller')){
            halt('控制器'.$this->c.'不存在');
        }
        $controllerClass = $this->c.'Controller';
        $controller = new $controllerClass();
        if(!method_exists($controller, $this->a.'Action')){
            halt('方法'.$this->a.'不存在');
        }
        call_user_func(array($controller,$this->a.'Action'));
    }
    /**
     * 自动加载函数
     * @param string $class 类名
     */
    public static function autoload($class){
        if(substr($class,-10)=='Controller'){
            includeIfExist(C('APP_FULL_PATH').'/Controller/'.$class.'.class.php');
        }elseif(substr($class,-6)=='Widget'){
            includeIfExist(C('APP_FULL_PATH').'/Widget/'.$class.'.class.php');
        }else{
            includeIfExist(C('APP_FULL_PATH').'/Lib/'.$class.'.class.php');
        }
    }
}
/**
 * 控制器类
 */
class Controller {
    /**
     * 构造函数，调用hook
     */
    public function __construct(){
        $this->_init();
    }
    /**
     * 前置hook
     */
    protected function _init(){}
    /**
     * 将数据用json格式输出至浏览器，并停止执行代码
     * @param array $data 要输出的数据
     */
    protected function ajaxReturn($data){
        echo json_encode($data);
        exit;
    }
    /**
     * 重定向至指定url
     * @param string $url 要跳转的url
     * @param void
     */
    protected function redirect($url){
        header("Location: $url");
        exit;
    }
}
/**
 * ExtException类，记录额外的异常信息
 */
class ExtException extends Exception{
    /**
     * @var array
     */
    protected $extra;
    /**
     * @param string $message
     * @param array $extra
     * @param int $code
     * @param null $previous
     */
    public function __construct($message = "", $extra = array(), $code = 0, $previous = null){
        $this->extra = $extra;
        parent::__construct($message, $code, $previous);
    }
    /**
     * 获取额外的异常信息
     * @return array
     */
    public function getExtra(){
        return $this->extra;
    }
}
