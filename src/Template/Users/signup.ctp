<?php
 echo $this->Html->script('submit_button.js');
 echo $this->Html->script('https://ajaxzip3.github.io/ajaxzip3.js');
?>
<div class="users form large-9 medium-8 columns content">
    <?= $this->Form->create($user) ?>
    <fieldset>
        <legend>新規管理ユーザー作成</legend>
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
                'label' => 'パスワード'
            ]);
            echo $this->Form->control('password_confirm',[
                'type' => 'password',
                'label' => 'パスワード（確認）'
            ]);
            echo $this->Form->hidden('level', [
                'value' => 1
            ]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('作成')) ?>
    <?= $this->Form->end() ?>
</div>
