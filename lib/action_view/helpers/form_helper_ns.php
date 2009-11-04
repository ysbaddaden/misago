<?php
# This file is never actually loaded, it's just for pdoc.

# Helpful functions to render form fields for a model.
# 
#   <\? $search = new Search() ?\>
#   <\?= form_tag(search_path()) ?\>
#     <p>
#       <\?= label($search, 'query') ?\>
#       <\?= text_field($search, 'query') ?\>
#       <\?= submit_tag() ?\>
#     </p>
#   </form>
# 
namespace ActionView\Helpers\FormHelper;

?>
