<?php
class AccountAction extends AppAction {
    public function index() {
        $this->redirect('Account/signin');
    }

    public function signin() {
        $this->display();
    }

    public function signup() {
        $this->display();
    }
}
