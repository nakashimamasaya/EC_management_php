<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $user
 */
?>
<div class="users form large-9 medium-8 columns content">
    <?= $this->Form->create($user) ?>
    <fieldset>
        <legend><?= __('Edit User') ?></legend>
        <?php
            echo $this->Form->control('lastname', [
                'label' => '氏名'
            ]);
            echo $this->Form->control('firstname', [
                'label' => '名前'
            ]);
            echo $this->Form->control('email', [
                'label' => 'メールアドレス'
            ]);
            echo $this->Form->control('postNumber', [
                'label' => '郵便番号',
                'onKeyUp' => "AjaxZip3.zip2addr(this,'','prececture','address');"
            ]);
            echo $this->Form->control('prececture', [
                'label' => '都道府県'
            ]);
            echo $this->Form->control('address', [
                'label' => '以降の住所'
            ]);
            echo $this->Form->control('password', [
                'label' => 'パスワード',
                'value' => ''
            ]);
            echo $this->Form->control('password_confirm',[
                'type' => 'password',
                'label' => 'パスワード（確認）'
            ]);
            if($current_user['level'] == 2 && $current_user['id'] != $user['id']){
                echo '<p>ユーザーレベル</p>';
                echo $this->Form->select('level',
                    [1 => '出品者ユーザ', 2 => 'adminユーザ'],
                    []
                );
            }else{
                echo $this->Form->control('level',[
                    'type' => 'hidden'
                ]);
            }
        ?>
    </fieldset>
    <?= $this->Form->button(__('送信')) ?>
    <?= $this->Form->end() ?>
</div>
