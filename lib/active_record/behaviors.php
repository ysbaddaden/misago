<?php

# Database object abstraction.
# 
# Permits to handle database entries like objects. It supports
# CRUD operations (create, read, update and delete), validations
# through ActiveRecord_Validations, and relations through
# ActiveRecord_Associations.
# 
# =CRUD
# 
# All the examples will use this single model:
# 
#   class Post extends ActiveRecord_Base
#   {
#   }
# 
# ==Create
# 
# You can create a new record, then save it:
# 
#   $post = new Post(array('title' => 'aaa', 'body' => 'bbb'));
#   $post->save();
# 
# Or you can create it directly:
#   
#   $post = new Post();
#   $new_post = $post->create(array('title' => 'aaa', 'body' => 'bbb'));
# 
# 
# ==Read
# 
# ===Find one
# 
# All the following methods will return a single post. As a matter of fact,
# they all return the same post (in these examples):
# 
#   $post = new Post(1);
#   $post = $post->find(1);
#   $post = $post->find(':first', array('conditions' => array('id' => 1)));
#   $post = $post->find(':first', array('conditions' => 'id = 1'));
#   $post = $post->find_by_id(1);
# 
# ===Find all
# 
# The following methods will return a collection of posts:
# 
#   $post  = new Post();
#   $posts = $post->find();
#   $posts = $post->find(':all');
#   $posts = $post->find(':all', array('order' => 'created_at desc', 'limit' => 25));
#   $posts = $post->find_all_by_category('aaa');
#   $posts = $post->find_all_by_category('aaa', array('order' => 'title asc'));
# 
# ===Scopes
# 
# Scopes are predefined options for find requests.
# 
# 
# ====Default scope
# 
# You may define a default scope for all finds. In the following example,
# all find requests to Comment will be returned ordered by creation date:
# 
#   class Comment extends ActiveRecord_Base
#   {
#     protected $default_scope = array('order' => 'created_at asc');
#   }
# 
# Attention:
# 
# - once a default scope has been defined all find requests will be affected. This could be troublesome, sometimes.
# - the default scope also affects the 'include' option, which shall be pretty convenient.
# 
# 
# ====Named scopes
# 
# [TODO]
# 
# 
# ==Update
# 
# There are several ways to update a record.
# 
#   $post = new Post();
#   $updated_post = $post->update(3, array('category' => 'ccc'));
#   
#   $post = new Post(2);
#   $post->title = 'abcd';
#   $post->save();
#   
#   $post = new Post(4);
#   $post->update_attributes(array('category' => 'ddd'));
#   
# Check update(), update_attribute() and update_attributes() for
# more examples.
# 
# 
# ==Delete
# 
# There are two ways to delete records: delete or destroy.
# 
# The difference is that delete always instanciates the record
# before deletion, permitting to interact with it. To delete an
# uploaded photo when deleting an image from a web gallery for
# instance.
# 
# On the contrary, destroy will delete all records at once in
# the database. There is no way to interact with the deletion
# of a particular entry.
# 
# The advantage of delete is to be able to interact with the
# deletion, but the advantage of destroy is it should be faster,
# especially when deleting many records.
# 
# ===delete
#
#   $post = new Post(5);
#   $post->delete();
# 
#   $post = new Post();
#   $post->delete(3);
# 
#   $post = new Post();
#   $post->delete_all(array('category' => 'aaa'));
#   $post->delete_all(array('category' => 'bbb', array('limit' => 5, 'order' => 'created_at desc'));
# 
# ===destroy
# 
#   $post = new Post(5);
#   $post->destroy();
# 
#   $post = new Post();
#   $post->destroy(3);
# 
#   $post = new Post();
#   $post->destroy_all(array('category' => 'aaa'));
#   $post->destroy_all(array('category' => 'bbb', array('limit' => 5, 'order' => 'created_at desc'));
# 
# ==Callbacks
# 
# Callbacks are hooks inside the lifecycle of an action to the record.
# 
# For instance when saving a new record:
# 
# - save()
# - is_valid()
# - [1] before_validation()
# - [2] before_validation_on_create()
# - validation()
# - validation_on_create()
# - [3] after_validation_on_create()
# - [4] after_validation()
# - [5] before_save()
# - [6] before_create()
# - create()
# - [7] after_create()
# - [8] after_save()
# 
# As you can see, there is a lot of callbacks, which permits you to
# interact with the creation process at every step of it. Same goes
# for update, which as the same lifecycle, but uses particular
# `on_update` callbacks instead of `on_create`.
# 
# Delete has callbacks too. But the lifecycle is simplier:
# 
# - delete()
# - [1] before_delete()
# - *actually deletes the entry*
# - [2] after_delete()
# 
# Remember that only delete has callbacks, destroy has no such methods.
# 
# 
# TODO: Implement calculations (count, max, sum, etc).
# TODO: Named scopes.
# IMPROVE: Test callbacks.
# 
abstract class ActiveRecord_Behaviors extends ActiveRecord_Validations
{
  protected $behaviors = array();
  
  function __construct()
  {
    foreach($this->behaviors as $behavior => $associations)
    {
      $name = String::camelize($behavior);
      $class = "ActiveRecord_Behaviors_{$behavior}_Base";
      
      foreach($associations as $assoc) {
        new $class($this, $this->associations[$assoc]);
      }
    }
  }
}

?>
