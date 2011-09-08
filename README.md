StickyForm module for Kohana 3.1
================================

Authors: Team [Kodeplay](http://kodeplay.com)

### About

Stickyform is a Kohana module for quickly setting up html forms with stickyfields and 
field specific inline errors. 

The form fields will be populated with values from post data, saved data and default data as follows
1. if form is submitted and validation fails -> posted data
2. when form is first shown for editing -> saved data
3. when form is shown for creation of a new entry -> default data

The 3rd parameter passed to the constructor is an array of errors obtained from the Validation instance
if form validation fails. Hence this is to be passed only if the form is submitted otherwise assumed an
empty array.

Refer to the simple example below for using Stickyform with a user create/edit form

### Example:

In controlller :

```php

$form = new Stickyform($action, array('id' => 'user-form', 'class' => 'frm'), ($is_submitted ? $validator->errors('user') : array()));

$form->default_data = array_fill_keys(array('firstname', 'lastname', 'email'), '');

$form->saved_data = $data_from_db;

$form->posted_data = $submitted ? $this->request->post() : array();

$form->append('First Name', 'firstname', 'text')
    ->append('Last Name', 'lastname', 'text')
    ->append('Email', 'email', 'text')
    ->append('Save', 'save', 'submit', array('attributes' => array('class' => 'button')));
    ->process();
    
```

In view :
```html

```php
<?php echo $form->startform(); ?>
```

<table class="formcontainer">

    <tr>
	<td><?php echo $form->firstname->label(); ?></td>
	<td><?php echo $form->firstname->element(); ?>
             <span class="form-error"><?php echo $form->firstname->error(); ?></span>
        </td>
    </tr>
    
    <tr>
	<td><?php echo $form->lastname->label(); ?></td>
	<td><?php echo $form->lastname->element(); ?>
	    <span class="form-error"><?php echo $form->lastname->error(); ?></span>
        </td>
    </tr>
    
    <tr>    
	<td><?php echo $form->email->label(); ?></td>
	<td><?php echo $form->email->element(); ?>
	    <span class="form-error"><?php echo $form->email->error(); ?></span>
        </td>
    </tr>
    
    <tr>
	<td></td>
	<td><?php echo $form->save->element(); ?></td>
    </tr>
    
<table>
```php
<?php echo $form->endForm(); ?>
```
```

Any feedback or contribution is welcome.




