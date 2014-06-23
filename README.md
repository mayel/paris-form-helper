Paris Form Helper
=================

Form helper for [Paris ORM](http://github.com/j4mie/paris) and inspired by [ActiveView::Helpers::FormHelper](http://api.rubyonrails.org/classes/ActionView/Helpers/FormHelper.html).  
It can be use without Paris.


## Example

A Paris Model

```php
<?php

/**
 * Post table columns:
 * - id
 * - title
 * - content
 * - author
 * - is_publish
 */
class Post extends Model {}

```

View example

```php
<?php $post = Model::factory('Post')->create(); ?>
...
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
```

## How use it?

### Start the form

To start the form use `Helpers_Form::form_for($model, $form_action, array $options)`.  
`$model`: the model to use for the form. So form values ares automaticaly setted. It can be null for standalone forms.    
`$form_action`: url to send the datas.  
`$options`: the options of the forms. The options are:
  * **method**: to precise the form method (post|get). Note: if you use Paris Model method are auto-setted **post** if the model is new and **put** for edition.
  * **html**: to add attributes on `<form />` tag.
And it return the instance of the form. It necessary to access the form helper methods.


To close the form use `Helpers_Form::end_form_for()` for print it.


### Label

`$form->label(string $name [ string $label, [, array $attributes [, array $options ]]])`


### Inputs

List of inputs: [checkbox](#checkbox) - [email](#email) - [hidden](#hidden) - [password](#password) - [text](#text)


#### Hidden


#### Text

`$form->text_field(string $name [, array $attributes [, array $options ]])`

#### Email

`$form->email_field(string $name [, array $attributes [, array $options ]])`

#### Password

`$form->password_field(string $name [, array $attributes [, array $options ]])`

#### Checkbox

`$form->checkbox(string $name [, array $attributes [, array $options ]])`


### TextArea

`$form->textarea(string $name [, array $attributes [, array $options ]])`

### Submit

`$form->submit(string $label [, array $attributes ])`

