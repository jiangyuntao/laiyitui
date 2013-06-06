<?php
class AppAction extends Action {
    protected $referer = '';
    protected $auth = array();
    protected $data = array();

    public function __construct() {
        parent::__construct();

        // uploadify hack。由于客户端不同，只能手动传递 session
        if (isset($_GET['PHPSESSIONID'])) {
            session_id($_GET['PHPSESSIONID']);
        }
        session_start();

        load('extend');
        import('ORG.Crypt.Crypt');
        import('ORG.Util.Cookie');

        $this->_authorize();
        $this->_getSettings();

        // 获取来路
        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->referer = $_SERVER['HTTP_REFERER'];
        } else {
            $this->referer = __SELF__;
        }
        $this->data['referer'] = $this->referer;

        if (ACTION_NAME == 'index') {
            $var_page = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
            $_SESSION['listpage'] = $var_page . '=' . ($_GET[$var_page] ? intval($_GET[$var_page]) : 1);
        }

        $this->assign('waitSecond', 2);
    }

    /**
     * 返回 JSON
     *
     * @param mixed $data
     * @access protected
     * @return void
     */
    protected function jsonReturn($data = null) {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
        header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
        header("Cache-Control: no-cache, must-revalidate" );
        header("Pragma: no-cache" );
        header("Content-type: text/x-json");
        exit(json_encode($data));
    }

    /**
     * 授权处理
     *
     * @access protected
     * @return void
     */
    protected function _authorize() {
        $this->data['auth'] = $this->auth = unserialize(Crypt::decrypt(COOKIE::get('auth'), C('SALT'), true));

        if (!$this->auth) {
            if (strtolower(MODULE_NAME) == 'index') {
                $this->redirect('/admin/account/signin');
            } else {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('/admin/account/signin'));
                return $this->error('对不起，您还没有登录！');
            }
        }
    }

    /**
     * 获取所有系统配置项
     *
     * @access protected
     * @return void
     */
    protected function _getSettings() {
        $setting = D('Setting');
        $settings = $setting->select();
        foreach ($settings as $v) {
            $this->data['setting'][$v['variable']] = $v['value'];
        }
    }
}
