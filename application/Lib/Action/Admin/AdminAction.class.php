<?php
/**
 * AdminAction
 *
 * @uses AppAction
 * @version $Id$
 * @author Thor Jiang <jiangyuntao@gmail.com>
 */
class AdminAction extends AppAction {
    protected $offset = 15;

    public function index() {
        $admin = D('Admin');

        import('ORG.Util.Page');

        $condition = "";
        if (isset($_GET['q'])) {
            $condition .= "username LIKE '%{$_GET['q']}%'";
            $this->data['q'] = $_GET['q'];
        }

        $admin_count = $admin->where($condition)->count('id');
        $page = new Page($admin_count, $this->offset);
        $page->parameter = 'q=' . urlencode($_GET['q']);

        $this->data['list'] = $admin->where($condition)
            ->order('id desc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->data['pagination'] = $page->show();

        $this->assign($this->data);
        $this->display();
    }

    public function create() {
        $admin = D('Admin');

        if ($this->isPost()) {
            if ($admin->where("username='{$_POST['username']}'")->find()) {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('admin/create'));
                return $this->error('创建失败！该管理员已存在');
            }

            if ($admin->create() && $id = $admin->add()) {
                $admin->find($id);
                $admin->sortorder = $id;
                $admin->save();
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('admin/index'));
                return $this->success('创建成功');
            } else {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('admin/create'));
                return $this->error('创建失败');
            }
        }

        $this->assign($this->data);
        $this->display();
    }

    public function modify() {
        $admin = D('Admin');

        if ($this->isPost()) {
            if ($admin->where("username='{$_POST['username']}'")->find() && $admin->id != $_POST['id']) {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('admin/create'));
                return $this->error('创建失败！该管理员已存在');
            }

            if ($_POST['password']) {
                $_POST['password'] = $admin->encryptPassword($_POST['password']);
            } else {
                $admin->find($_POST['id']);
                $_POST['password'] = $admin->password;
            }

            $this->assign('waitSecond', 2);
            $this->assign('jumpUrl', U('admin/modify', 'id=' . $_GET['id']));
            if ($admin->create() && $admin->save()) {
                return $this->success('修改成功');
            } else {
                return $this->error('修改失败');
            }
        }

        $this->data['admin'] = $admin->find($_GET['id']);

        $this->assign($this->data);
        $this->display();
    }

    public function remove() {
        $admin = D('Admin');
        if ($admin->count('id')) {
            $this->assign('waitSecond', 2);
            $this->assign('jumpUrl', U('admin/index'));
            return $this->error('删除失败！目前仅有一个管理员，不可删除');
        }

        if ($admin->delete($_GET['id'])) {
            $this->assign('waitSecond', 2);
            $this->assign('jumpUrl', U('admin/index'));
            return $this->success('删除成功');
        } else {
            $this->assign('waitSecond', 2);
            $this->assign('jumpUrl', U('admin/modify', 'id=' . $_GET['id']));
            return $this->error('删除失败' . $admin->getError());
        }
    }
}
