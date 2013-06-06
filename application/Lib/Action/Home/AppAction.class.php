<?php
class AppAction extends Action {
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

        // 获取用户登录信息
        $this->auth = $this->auth = unserialize(Crypt::decrypt(COOKIE::get('auth'), C('SALT'), true));

        // 获取来路
        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->referer = $_SERVER['HTTP_REFERER'];
        } else {
            $this->referer = __SELF__;
        }

        if (ACTION_NAME == 'index') {
            $var_page = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
            $_SESSION['listpage'] = $var_page . '=' . ($_GET[$var_page] ? intval($_GET[$var_page]) : 1);
        }
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
}
