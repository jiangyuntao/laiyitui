<?php
class CategoryAction extends AppAction {
    protected $offset = 15;

    public function index() {
        $category = D('Category');

        import('ORG.Util.Page');

        $condition = '';
        if (isset($_GET['q'])) {
            $condition .= "name LIKE '%{$_GET['q']}%'";
            $this->data['q'] = $_GET['q'];
        }

        $category_count = $category->where($condition)->count('id');
        $page = new Page($category_count, $this->offset);
        if (isset($_GET['q'])) {
            $page->parameter = 'q=' . urlencode($_GET['q']);
        }

        $this->data['list'] = $category->where($condition)
            ->order('sortorder desc, id desc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->data['pagination'] = $page->show();

        $this->assign($this->data);
        $this->display();
    }

    public function create() {
        $category = D('Category');

        if ($this->isPost()) {
            if ($category->create() && $id = $category->add()) {
                // 根据 id 更新 sortorder
                $category->find($id);
                $category->sortorder = $id;
                $category->save();

                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('/admin/category/index'));
                return $this->success('创建成功');
            } else {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('/admin/category/create'));
                return $this->error('创建失败');
            }
        }

        $this->assign($this->data);
        $this->display();
    }

    public function modify() {
        $category = D('Category');

        if ($this->isPost()) {
            if ($category->create() && $category->save()) {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('/admin/category/modify', 'id=' . $_GET['id']));
                return $this->success('修改成功');
            } else {
                return $this->error('修改失败');
            }
        }

        $this->data['category'] = $category->find($_GET['id']);

        $this->assign($this->data);
        $this->display();
    }

    public function remove() {
        $category = D('Category');

        if ($category->delete($_GET['id'])) {
            $this->assign('waitSecond', 2);
            $this->assign('jumpUrl', U('/admin/category/index'));
            return $this->success('删除成功');
        } else {
            $this->assign('waitSecond', 2);
            $this->assign('jumpUrl', U('/admin/category/index', $_SESSION['listpage']));
            return $this->error('删除失败' . $category->getError());
        }
    }

    public function up() {
        $category = D('Category');

        $current = $category->find($_GET['id']);
        $upper = $category->where("sortorder>'{$current['sortorder']}'")->order('sortorder asc, id asc')->limit(1)->find();

        if ($upper) {
            $tmp = $current['sortorder'];
            $current['sortorder'] = $upper['sortorder'];
            $upper['sortorder'] = $tmp;
            $category->save($upper);
            $category->save($current);
        }

        redirect(U('/admin/category/index', $_SESSION['listpage']));
    }

    public function down() {
        $category = D('Category');

        $current = $category->find($_GET['id']);
        $upper = $category->where("sortorder<'{$current['sortorder']}'")->order('sortorder desc, id desc')->limit(1)->find();

        if ($upper) {
            $tmp = $current['sortorder'];
            $current['sortorder'] = $upper['sortorder'];
            $upper['sortorder'] = $tmp;
            $category->save($upper);
            $category->save($current);
        }

        redirect(U('/admin/category/index', $_SESSION['listpage']));
    }
}
