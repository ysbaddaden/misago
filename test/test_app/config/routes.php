<?php
#
# Priority is based upon order of creation.
#

$map = Misago\ActionController\Routing::draw();

# regular route:
#   $map->connect('products/:id', array(':controller' => 'catalog', ':action' => 'view'));

# named route (not implemented yet)
#   $map->named('purchase', 'products/:id/purchase', array(':controller' => 'catalog', ':action' => 'purchase'));

# RESTful resource
$map->resource('products');

# landing page
$map->root(array(':controller' => 'welcome'));

# default routes
$map->connect(':controller/:action/:id.:format');

?>
