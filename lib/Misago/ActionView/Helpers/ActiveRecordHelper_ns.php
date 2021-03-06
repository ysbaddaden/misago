<?php

# Helper object to create a HTML form associated to a record.
# 
# Initiate helper for a class:
# 
#   $f = form_for('User');
# 
# Initiate helper for an instance:
# 
#   $user = new User(456);
#   $f = form_for($user);
# 
# Build a form:
# 
#   $f->start(update_user_path($user->id))
#   $f->label('username');
#   $f->text_field('username');
#   submit_tag('Save');
#   $f->end();
# 
# You may also mix records:
# 
#   $p = fields_for($user->profile);
#   $f->start(update_user_path($user->id))
#   
#   $f->label('username');
#   $f->text_field('username');
#   
#   $p->label('about');
#   $p->text_field('about');
#   
#   $f->submit('Save');
#   $f->end();
# 
namespace Misago\ActionView\Helpers\ActiveRecordHelper;

?>
