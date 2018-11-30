<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;

class UsersController extends AppController
{

    public function beforeFilter(Event $event){
        parent::beforeFilter($event);
        $before_login = ['login','signup'];
        $this->Auth->allow($before_login);
        $user = $this->Auth->user();
        if(in_array($this->request->action, $before_login)){
            if(isset($user)){
                $this->Flash->success('すでにログインしています');
                $this->redirect(['controller'=>'Products','action'=>'index']);
            }
        }
        else if($this->request->action == 'index'){
            if($user['level'] != 2){
                $this->Flash->error('権限がありません。');
                return $this->redirect(['controller' => 'Products', 'action' => 'index']);
            }
        }
    }

    public function index()
    {
        $users = $this->paginate($this->Users);
        $current_user = $this->Auth->user();
        $this->set(compact('users', 'current_user'));
    }

    public function view($id = null)
    {
        try{
            $user = $this->Users->get($id, [
                'contain' => []
            ]);
            $current_user = $this->Auth->user();
            if($current_user->level != 2 && $id != $current_user['id']){
                $this->Flash->error('権限がありません');
                return $this->redirect(['controller'=>'Products','action'=>'managements']);
            }
            $this->set(compact('user', 'current_user'));
        }catch(RecordNotFoundException $e){
            $this->Flash->error('ユーザが存在しません。');
            $this->redirect(['controller'=>'Products','action'=>'managements']);
        }
    }

    public function logout(){
        $this->Flash->success('ログアウトしました');
        return $this->redirect($this->Auth->logout());
    }

    public function signup()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('ユーザーが作成されました。'));

                return $this->redirect(['controller'=> 'Managements' , 'action' => 'login']);
            }
            $this->Flash->error(__('ユーザーの作成に失敗しました。'));
        }
        $this->set(compact('user'));
    }

    public function edit($id = null)
    {
        try{
            $current_user = $this->Auth->user();
            $user = $this->Users->get($id, [
                'contain' => []
            ]);
            if($id != $current_user['id'] && $current_user['level'] != 2 || !isset($user)){
                $this->redirect(['controller'=>'Products','action'=>'index']);
            }
            if ($this->request->is(['patch', 'post', 'put'])) {
                $user = $this->Users->patchEntity($user, $this->request->getData());
                if ($this->Users->save($user)) {
                    $this->Flash->success(__('変更が適用されました。'));

                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('変更されませんでした。もう一度お試しください。'));
            }
            $this->set(compact('user', 'current_user'));
        }catch(RecordNotFoundException $e){
            $this->Flash->error('ユーザが存在しません。');
            $this->redirect(['controller'=>'Products','action'=>'managements']);
        }
    }

    public function delete($id = null)
    {
        try{
            $current_user = $this->Auth->user();
            if($id != $current_user['id'] && $current_user['level'] != 2){
                $this->redirect(['controller'=>'Products','action'=>'index']);
            }
            $this->request->allowMethod(['post', 'delete']);
            $user = $this->Users->get($id);
            $flag = $id == $current_user['id'];
            if ($this->Users->delete($user)) {
                if($flag){
                    $this->Flash->success(__('退会しました。'));
                    return $this->redirect($this->Auth->logout());
                }else{
                    $this->Flash->success(__('ユーザを削除しました。'));
                }
            } else {
                $this->Flash->error(__('エラーが起きました。'));
            }

            return $this->redirect(['action' => 'index']);
        }catch(RecordNotFoundException $e){
            $this->Flash->error('ユーザが存在しません。');
            $this->redirect(['controller'=>'Products','action'=>'index']);
        }
    }
}
