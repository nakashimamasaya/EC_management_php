<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

/**
 * Categories Controller
 *
 * @property \App\Model\Table\CategoriesTable $Categories
 *
 * @method \App\Model\Entity\Category[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CategoriesController extends AppController
{
    public $components = ['UserLevel', 'ImageCheck'];

    public function beforeFilter(Event $event){
        parent::beforeFilter($event);
        $this->Auth->allow([]);
        if(!$this->UserLevel->checkUser($this->Auth->user())){
            $this->Flash->error('アクセス権限がありません。');
            $this->redirect(['controller' => 'products', 'action' => 'index']);
        }
    }

    public function index()
    {
        $categories = $this->paginate($this->Categories);
        $current_user = $this->Auth->user();

        $this->set(compact('categories', 'current_user'));
    }

    public function add()
    {
        $category = $this->Categories->newEntity();
        if ($this->request->is('post')) {
            $category = $this->Categories->patchEntity($category, $this->request->getData());
            if ($this->Categories->save($category)) {
                $this->Flash->success(__('カテゴリーが作成されました'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('カテゴリーの作成に失敗しました。'));
        }
        $this->set(compact('category'));
    }

    public function edit($id = null)
    {
        $category = $this->Categories->get($id, [
            'contain' => []
        ]);
        $this->checkAdminUser();
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $category = $this->Categories->patchEntity($category, $this->request->getData());
            if ($this->Categories->save($category)) {
                $this->Flash->success(__('編集が保存されました。'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('カテゴリーの作成に失敗しました。'));
        }
        $this->set(compact('category'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->checkAdminUser();
        $category = $this->Categories->get($id);
        if ($this->Categories->delete($category)) {
            $this->Flash->success(__('削除しました。'));
        } else {
            $this->Flash->error(__('削除に失敗しました。'));
        }

        return $this->redirect(['action' => 'index']);
    }

    private function checkAdminUser(){
        $current_user = $this->Auth->user();
        if($current_user['level'] != 2){
            $this->Flash->error(__('権限がありません。'));
            return $this->redirect(['action' => 'index']);
        }
    }
}
