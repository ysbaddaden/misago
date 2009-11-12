<?php

if (!isset($_SERVER['argv'][1])) {
  $_SERVER['argv'][1] = 'help';
}

switch($_SERVER['argv'][1])
{
  case 'install':
    Misago_Plugin::install($_SERVER['argv'][2]);
  break;
  
  case 'update':
    Misago_Plugin::update($_SERVER['argv'][2]);
  break;
  
  case 'uninstall':
    Misago_Plugin::uninstall($_SERVER['argv'][2]);
  break;
  
  default: 
    echo "Syntax error:\n";
    echo "  $ script/plugin [options] <command> <URL>\n";
    echo "\n";
    echo "Examples:\n";
    echo "  $ script/plugin install http://example.com/misago/plugins/tag_behavior\n";
    echo "  $ script/plugin update http://example.com/misago/plugins/tag_behavior\n";
    echo "  $ script/plugin uninstall tag_behavior\n";
  die();
}

?>
