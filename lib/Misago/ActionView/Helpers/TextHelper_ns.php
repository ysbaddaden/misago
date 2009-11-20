<?php

namespace Misago\ActionView\Helpers\TextHelper;

# :nodoc:
function replace_urls($match)
{
  return link_to($match[0]);
}

# :nodoc:
function replace_email_addresses($match)
{
  return mail_to($match[0]);
}

?>
