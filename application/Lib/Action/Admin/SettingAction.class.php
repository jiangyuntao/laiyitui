<?php
/**
 * SettingAction
 *
 * @uses AppAction
 * @version $Id$
 * @author Thor Jiang <jiangyuntao@gmail.com>
 */
class SettingAction extends AppAction {
    public function index() {
        $setting = D('Setting');

        if ($this->isPost()) {
            foreach ($this->data['setting'] as $k => $v) {
                if ($_POST[$k] == $v) {
                    continue;
                }

                $setting->where("variable='{$k}'")->save(array(
                    'value' => $_POST[$k],
                    'modified' => time(),
                ));
            }

            $this->assign('waitSecond', 2);
            $this->assign('jumpUrl', U('setting/index'));
            return $this->success('修改成功');
        }

        $this->assign($this->data);
        $this->display();
    }
}
