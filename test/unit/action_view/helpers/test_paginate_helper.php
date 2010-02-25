<?php
require_once __DIR__.'/../../../unit.php';
require_once MISAGO."/lib/Misago/ActionView/Helpers/TagHelper.php";
require_once MISAGO."/lib/Misago/ActionView/Helpers/UrlHelper.php";
require_once MISAGO."/lib/Misago/ActionView/Helpers/PaginateHelper.php";

class Test_ActionView_Helpers_PaginateHelper extends Misago\Unit\TestCase
{
  function test_paginate()
  {
    $posts = Post::paginate(array('page' => 3));
    $this->assert_null(paginate($posts));

    $posts = Post::paginate(array('page' => 1, 'per_page' => 2, 'order' => 'id'));
    $this->assert_equal(paginate($posts, array('params' => array(':controller' => 'articles'), 'page_links' => false)),
      '<ul class="pagination">'.
        '<li class="previous">previous</li> '.
        '<li class="next"><a href="/articles?page=2">next</a></li>'.
      '</ul>');
    
    $posts = Post::paginate(array('page' => 2, 'per_page' => 2, 'order' => 'id'));
    $this->assert_equal(paginate($posts, array('id' => true, 'params' => array(':controller' => 'articles'), 'page_links' => false)),
      '<ul class="pagination" id="posts_pagination">'.
        '<li class="previous"><a href="/articles?page=1">previous</a></li> '.
        '<li class="next">next</li>'.
      '</ul>');
    
    $posts = Post::paginate(array('page' => 2, 'per_page' => 1, 'order' => 'id'));
    $this->assert_equal(paginate($posts, array('id' => true, 'params' => array(':controller' => 'articles'))),
      '<ul class="pagination" id="posts_pagination">'.
        '<li class="previous"><a href="/articles?page=1">previous</a></li> '.
        '<li><a href="/articles?page=1">1</a></li> '.
        '<li>2</li> '.
        '<li><a href="/articles?page=3">3</a></li> '.
        '<li class="next"><a href="/articles?page=3">next</a></li>'.
      '</ul>');
  }
}

?>
