<?php

require_once '../form_helper.php';
require_once '../paris.php';

/**
 * Post table columns:
 * - id
 * - title
 * - content
 * - author
 * - is_publish
 */
class Post extends Model {}


/**
 * View
 */

$post = Model::factory('Post')->create();
?>

<?php $form = Helpers_Form::form_for($post, '/posts/create', array('html' =>array('class' => 'form'))) ?>

  <?= $form->label('title') ?><br />
  <?= $form->text_field('title') ?>

  <?= $form->label('content') ?><br />
  <?= $form->text_area('content', array('cols' => 50, 'rows' => 4)) ?>

  <?= $form->label('author') ?><br />
  <?= $form->text_area('author', array('class' => 'author-input')) ?>

  <?= $form->label('is_publish') ?><br />
  <?= $form->text_area('is_publish', array('check_value' => 'yes', 'uncheck_value' => 'no')) ?>

<?php Helpers_Form::end_form_for() ?>
