<?php

/**
 * Message model
 */
class Message extends stdClass {
    public $title;
    public $author;
    public content;
}

$message = new Message();
?>

<?php $form = Helpers_Form::form_for($message, '', array('html' =>array('class' => 'form'))) ?>

  <?= $form->label('title') ?><br />
  <?= $form->text_field('title') ?>

  <?= $form->label('content') ?><br />
  <?= $form->text_area('content', array('cols' => 50, 'rows' => 4)) ?>

  <?= $form->label('author') ?><br />
  <?= $form->text_area('author', array('class' => 'author-input')) ?>

<?php Helpers_Form::end_form_for() ?>
