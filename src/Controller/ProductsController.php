<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use App\Model\Utility\S3Manager;

class ProductsController extends AppController
{
    public $components = ['UserLevel', 'ImageCheck'];


    public function beforeFilter(Event $event){
        parent::beforeFilter($event);
        $this->Auth->allow([]);
    }

    public function managements(){
        $current_user = $this->Auth->user();
        if($this->UserLevel->checkUser($current_user)){
            if($current_user['level'] == 2){
                $query = $this->Products;
            }
            else{
                $query = $this->Products->find()->where(['user_id' => $current_user['id']]);
            }
            $products = $this->paginate($query);
            $this->set(compact('products'));
        }
        else{
            $this->redirect(['action' => 'index']);
        }
    }

    public function view($id = null)
    {
        $product = $this->Products->get($id, [
            'contain' => ['CategoriesProduct' => ['Categories']]
        ]);

        $this->set('product', $product);
    }

    public function add()
    {
        if($this->UserLevel->checkUser($this->Auth->user())){
            $this->loadModel('Categories');
            $this->loadModel('CategoriesProduct');
            $product = $this->Products->newEntity();
            $lists = $this->Categories->find('all');
            $array = [];
            foreach ($lists as $list) {
                $array += [$list['id'] => $list['name']];
            }
            if ($this->request->is('post')) {
                $product = $this->Products->patchEntity($product, $this->request->getData());
                $fileName = $this->request->getData('image');
                $type = $this->ImageCheck->checkImage($fileName, $fileName['error']);

                if($type == 0){
                    $s3 = new S3Manager();
                    $file = $fileName['name'];
                    $result = $s3->putObject($fileName['tmp_name'], $file, $file);
                    $product->img = $result;
                }else if($type == 1){
                    $this->Flash->error('画像は２MB未満の「png, jpeg, gif」のみです。');
                }else{
                    $product->img = env('DEFAULT_IMAGE_PATH');
                }

                if ($this->Products->save($product)) {
                    $this->Flash->success(__('商品が作成されました。'));

                    foreach ($this->request->getData('categories') as $list) {
                        $categories_product = $this->CategoriesProduct->newEntity();
                        $categories_product->set([
                            'product_id' => $product->id,
                            'category_id' => $list
                        ]);
                        $this->CategoriesProduct->save($categories_product);
                    }

                    return $this->redirect(['action' => 'managements']);
                }
                $this->Flash->error(__('作成できませんでした。'));
            }
            $this->set(compact('product', 'array'));
            $this->set('users', $this->Auth->user());
            $this->set('value', '');
        }
        else{
            $this->Flash->error('アクセス権限がありません。');
            return $this->redirect(['action' => 'index']);
        }
    }

    public function edit($id = null)
    {
        $this->loadModel('Categories');
        $this->loadModel('CategoriesProduct');
        $current_user = $this->Auth->user();
        $lists = $this->Categories->find('all');
        $product = $this->Products->get($id, [
            'contain' => ['CategoriesProduct']
        ]);
        $value = [];
        foreach($product->categories_product as $category){
            array_push($value, $category->category_id);
        }

        if($this->UserLevel->checkEditUser($current_user, $product)){
            $array = [];
            foreach ($lists as $list) {
                $array += [$list['id'] => $list['name']];
            }
            if ($this->request->is(['patch', 'post', 'put'])){

                $product = $this->Products->patchEntity($product, $this->request->getData());
                $fileName = $this->request->getData('image');
                $type = $this->ImageCheck->checkImage($fileName, $fileName['error']);
                if($type == 0){
                    $s3 = new S3Manager();
                    $file = $fileName['name'];
                    $result = $s3->putObject($fileName['tmp_name'], $file, $file);
                    $product->img = $result;
                }else if($type == 1){
                    $this->Flash->error('画像は２MB未満の「png, jpeg, gif」のみです。');
                    return $this->redirect(['action' => 'edit', $id]);
                }

                if ($this->Products->save($product)) {
                    $this->Flash->success(__('保存されました。'));
                    foreach($product->categories_product as $intermediate){
                        $this->CategoriesProduct->delete($intermediate);
                    }
                    foreach ($this->request->getData('categories') as $list) {
                        $categories_product = $this->CategoriesProduct->newEntity();

                        $categories_product->set([
                            'product_id' => $product->id,
                            'category_id' => $list
                        ]);
                        $this->CategoriesProduct->save($categories_product);
                    }

                    return $this->redirect(['action' => 'managements']);
                }
                $this->Flash->error(__('保存されませんでした。'));
            }
            $this->set(compact('product', 'array', 'value'));
            $this->set('users', $this->Auth->user());
        }
        else{
            $this->Flash->error('アクセス権限がありません。');
            return $this->redirect(['action' => 'managements']);
        }
    }

    public function delete($id = null)
    {
        $current_user = $this->Auth->user();
        $product = $this->Products->get($id);
        if($current_user['level'] == 2 || $product->user_id == $current_user['id']){
            $this->request->allowMethod(['post', 'delete']);
            if ($this->Products->delete($product)) {
                $this->Flash->success(__('削除されました。'));
            } else {
                $this->Flash->error(__('削除されませんでした。'));
            }
        }
        else{
            $this->Flash->error('アクセス権限がありません。');
        }

        return $this->redirect(['action' => 'managements']);
    }
}
