<?php
/**
 * CategoryAction
 *
 * @uses AppAction
 * @version $Id$
 * @author Thor Jiang <jiangyuntao@gmail.com>
 */
class CategoryAction extends AppAction {
    protected $offset = 15;

    public function index() {
        $categoryView = D('CategoryView');

        import('ORG.Util.Page');

        $condition = "language='{$this->data['setting']['default_language']}'";
        if (isset($_GET['q'])) {
            $condition .= " && (title LIKE '%{$_GET['q']}%' || content LIKE '%{$_GET['q']}%')";
            $this->data['q'] = $_GET['q'];
        }

        $category_count = $categoryView->where($condition)->count('c.id');
        $page = new Page($category_count, $this->offset);
        if (isset($_GET['q'])) {
            $page->parameter = 'q=' . urlencode($_GET['q']);
        }

        $this->data['list'] = $categoryView->where($condition)
            ->order('id desc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->data['pagination'] = $page->show();

        $this->assign($this->data);
        $this->display();
    }

    public function create() {
        $category = D('Category');

        if ($this->isPost()) {
            // 主表添加信息
            if ($category->create() && $id = $category->add()) {
                // 处理 tag
                if ($_POST['tag']) {
                    $tag = D('Tag');
                    $tag->process($_POST['tag'], $id, 'category');
                }

                // 添加不同语言内容
                foreach ($this->data['language'] as $lang) {
                    $categoryLocal = D('CategoryLocal');

                    $categoryLocal->category_id = $id;
                    $categoryLocal->language = $lang['language'];
                    $categoryLocal->title = $_POST['title'][$lang['language']];
                    $categoryLocal->keywords = $_POST['keywords'][$lang['language']];
                    $categoryLocal->description = $_POST['description'][$lang['language']];
                    $categoryLocal->content = $_POST['content'][$lang['language']];
                    $categoryLocal->created = $categoryLocal->modified = time();

                    if (!$categoryLocal->add()) {
                        // 如果某一语言内容添加失败，删除之前添加的主表信息和其他语言内容
                        $category->where("id='{$id}'")->delete();
                        $categoryLocal->where("category_id='{$id}'")->delete();

                        $this->assign('waitSecond', 2);
                        $this->assign('jumpUrl', U('category/create'));
                        return $this->error('创建失败！');
                    }
                }

                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('category/index'));
                return $this->success('创建成功');
            } else {
                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('category/create'));
                return $this->error('创建失败');
            }
        }

        $this->assign($this->data);
        $this->display();
    }

    public function modify() {
        $category = D('Category');

        $this->data['category'] = $category->find($_GET['id']);

        if ($this->isPost()) {
            if (!isset($_POST['recommended']) || !$_POST['recommended']) {
                $_POST['recommended'] = '0';
            }

            // 如果上传了新图片，删除原图片
            if ($this->data['category']['picture'] != $_POST['picture']) {
                @unlink(realpath(__ROOT__) . $this->data['category']['picture']);
            }

            // 修改主表信息
            if ($category->create() && $category->save()) {
                // 处理 tag
                if ($_POST['tag']) {
                    $tag = D('Tag');
                    $tag->process($_POST['tag'], $_GET['id'], 'category');
                }

                // 修改不同语言内容
                foreach ($this->data['language'] as $lang) {
                    $categoryLocal = D('CategoryLocal');

                    $categoryLocal->where("category_id='{$_GET['id']}' && language='{$lang['language']}'")->find();
                    $categoryLocal->title = $_POST['title'][$lang['language']];
                    $categoryLocal->keywords = $_POST['keywords'][$lang['language']];
                    $categoryLocal->description = $_POST['description'][$lang['language']];
                    $categoryLocal->content = $_POST['content'][$lang['language']];
                    $categoryLocal->modified = time();

                    if (!$categoryLocal->save()) {
                        $this->assign('waitSecond', 2);
                        $this->assign('jumpUrl', U('category/modify', 'id=' . $_GET['id']));
                        return $this->error('修改失败！'. $categoryLocal->getError());
                    }
                }

                $this->assign('waitSecond', 2);
                $this->assign('jumpUrl', U('category/modify', 'id=' . $_GET['id']));
                return $this->success('修改成功');
            } else {
                return $this->error('修改失败');
            }
        }

        $categoryLocal = D('CategoryLocal');
        $locals = $categoryLocal->where("category_id='{$_GET['id']}'")->select();
        foreach ($locals as $v) {
            $this->data['category']['language'][$v['language']] = $v;
        }

        $this->assign($this->data);
        $this->display();
    }

    public function remove() {
        $category = D('Category');
        $category->find($_GET['id']);

        $categoryLocal = D('CategoryLocal');

        if ($category->delete() && $categoryLocal->where("category_id='{$_GET['id']}'")->delete()) {
            @unlink(realpath(__ROOT__) . $category->picture);
            $this->assign('waitSecond', 2);
            $this->assign('jumpUrl', U('category/index'));
            return $this->success('删除成功');
        } else {
            $this->assign('waitSecond', 2);
            $this->assign('jumpUrl', U('category/index', $_SESSION['listpage']));
            return $this->error('删除失败' . $category->getError());
        }
    }

    public function picture_upload() {
        if (!empty($_FILES)) {
            $tmp_file = $_FILES['Filedata']['tmp_name'];
            $ext = strtolower(substr(strrchr($_FILES['Filedata']['name'], '.'), 1));
            $target_path = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/' . date('Ym/d/');
            mkdir($target_path, 0777, true);
            $target_file =  str_replace('//', '/', $target_path) . md5($_FILES['Filedata']['name'] . microtime(true)) . '.' . $ext;
            $target_file_big =  str_replace('//', '/', $target_path) . md5($_FILES['Filedata']['name'] . 'big' . microtime(true)) . '.' . $ext;

            // 后缀检查
            $allowed_ext = array('gif', 'bmp', 'jpg', 'jpeg', 'png');
            if (!in_array($ext, $allowed_ext)) {
                $this->ajaxReturn('', '请上传图片文件', 0);
            }

            move_uploaded_file($tmp_file, $target_file);

            // 缩放图片
            vendor('WideImage.WideImage');
            WideImage::load($target_file)
                ->resize($this->data['setting']['category_big_picture_width'], $this->data['setting']['category_big_picture_height'])
                ->saveToFile($target_file_big);
            WideImage::load($target_file)
                ->resize($this->data['setting']['category_picture_width'], $this->data['setting']['category_picture_height'])
                ->saveToFile($target_file);

            $this->ajaxReturn(array(
                'picture' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $target_file),
                'big_picture' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $target_file_big)
            ), '', 1);
        }
    }
}
