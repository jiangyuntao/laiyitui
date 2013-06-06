<?php
class AccountAction extends Action {
    protected $data = array();

    public function __construct() {
        parent::__construct();
        import('ORG.Crypt.Crypt');
        import('ORG.Util.Cookie');
    }

    public function index() {
        $this->redirect('account/signin');
    }

    public function signin() {
        // 已登录用户，跳转到首页
        if (unserialize(Crypt::decrypt(COOKIE::get('auth'), C('SALT'), true))) {
            $this->redirect('index/index');
        }

        if ($this->isPost()) {
            $admin = D('Admin');
            if (!$admin->where("username='{$_POST['username']}'")->find()) {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('account/signin'));
                return $this->error('登录失败！用户名错误');
            }
            if ($admin->password != $admin->encryptPassword($_POST['password'])) {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('account/signin'));
                return $this->error('登录失败！密码错误');
            }

            if (isset($_POST['remember'])) {
                Cookie::set('auth', Crypt::encrypt(serialize(array(
                    'id' => $admin->id,
                    'username' => $admin->username,
                )), C('SALT'), true), $_POST['remember']);
            } else {
                Cookie::set('auth', Crypt::encrypt(serialize(array(
                    'id' => $admin->id,
                    'username' => $admin->username,
                )), C('SALT'), true));
            }

            $this->assign('waitSecond', 2);
            $this->assign('jumpUrl', U('index/index'));
            return $this->success('登录成功！');
        }

        $this->display();
    }

    public function signout() {
        Cookie::delete('auth');
        $this->redirect('account/signin');
    }

    public function change_password() {
        // 获取用户登录信息
        $this->data['auth'] = $this->auth = unserialize(Crypt::decrypt(COOKIE::get('auth'), C('SALT'), true));

        if ($this->isPost()) {
            $admin = D('Admin');
            $admin->where("id='{$this->auth['id']}'")->find();

            if ($admin->encryptPassword($_POST['password_original']) != $admin->password) {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('account/change_password'));
                return $this->error('修改密码失败！原密码错误');
            }

            $admin->password = $admin->encryptPassword($_POST['password']);
            $admin->modified = time();

            if ($_POST['password'] != $_POST['password_repeat']) {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('account/change_password'));
                return $this->error('修改密码失败！两次输入密码必须一致');
            }

            if ($admin->save()) {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('account/change_password'));
                return $this->success('修改密码成功！');
            } else {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('account/change_password'));
                return $this->error('修改密码失败！' . $admin->getError());
            }
        }
        $this->assign($this->data);
        $this->display();
    }
}
