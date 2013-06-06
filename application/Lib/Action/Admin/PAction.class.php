<?php
class PAction extends AppAction {
    protected $offset = 15;

    public function index() {
        $p = D('P');

        import('ORG.Util.Page');

        $condition = '';
        if (isset($_GET['q'])) {
            $condition .= "title LIKE '%{$_GET['q']}%'";
            $this->data['q'] = $_GET['q'];
        }

        $p_count = $p->where($condition)->count('p.id');
        $page = new Page($p_count, $this->offset);
        if (isset($_GET['q'])) {
            $page->parameter = 'q=' . urlencode($_GET['q']);
        }

        $this->data['list'] = $p->where($condition)
            ->order('sortorder desc, id desc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->data['pagination'] = $page->show();

        $this->assign($this->data);
        $this->display();
    }

    public function create() {
        $p = D('P');

        if ($this->isPost()) {
            if ($p->create() && $id = $p->add()) {
                // 根据 id 更新 sortorder
                $p->find($id);
                $p->sortorder = $id;
                $p->save();

                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('/admin/p/index'));
                return $this->success('创建成功');
            } else {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('/admin/p/create'));
                return $this->error('创建失败');
            }
        }

        $this->assign($this->data);
        $this->display();
    }

    public function modify() {
        $p = D('P');

        if ($this->isPost()) {
            if ($p->create() && $p->save()) {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('/admin/p/modify', 'id=' . $_GET['id']));
                return $this->success('修改成功');
            } else {
                return $this->error('修改失败');
            }
        }

        $this->data['p'] = $p->find($_GET['id']);

        $this->assign($this->data);
        $this->display();
    }

    public function remove() {
        $p = D('P');

        if ($p->delete($_GET['id'])) {
            $this->assign('waitSecond', 2);
            $this->assign('jumpUrl', U('/admin/p/index'));
            return $this->success('删除成功');
        } else {
            $this->assign('waitSecond', 2);
            $this->assign('jumpUrl', U('/admin/p/index', $_SESSION['listpage']));
            return $this->error('删除失败' . $p->getError());
        }
    }

    public function up() {
        $p = D('P');

        $current = $p->find($_GET['id']);
        $upper = $p->where("sortorder>'{$current['sortorder']}'")->order('sortorder asc, id asc')->limit(1)->find();

        if ($upper) {
            $tmp = $current['sortorder'];
            $current['sortorder'] = $upper['sortorder'];
            $upper['sortorder'] = $tmp;
            $p->save($upper);
            $p->save($current);
        }

        redirect(U('/admin/p/index', $_SESSION['listpage']));
    }

    public function down() {
        $p = D('P');

        $current = $p->find($_GET['id']);
        $upper = $p->where("sortorder<'{$current['sortorder']}'")->order('sortorder desc, id desc')->limit(1)->find();

        if ($upper) {
            $tmp = $current['sortorder'];
            $current['sortorder'] = $upper['sortorder'];
            $upper['sortorder'] = $tmp;
            $p->save($upper);
            $p->save($current);
        }

        redirect(U('/admin/p/index', $_SESSION['listpage']));
    }
}
