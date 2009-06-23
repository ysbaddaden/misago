<?php function users_path($keys=array())
{
  if (!is_array($keys)) {
    $keys = array(':id' => $keys);
  }
  $path = strtr('users.:format', $keys);
  $path = preg_replace('/[\/\.\?]:format/', '', $path);
  return new ActionController_Path('GET', $path);
}

function new_user_path($keys=array())
{
  if (!is_array($keys)) {
    $keys = array(':id' => $keys);
  }
  $path = strtr('users/new.:format', $keys);
  $path = preg_replace('/[\/\.\?]:format/', '', $path);
  return new ActionController_Path('GET', $path);
}

function show_user_path($keys=array())
{
  if (!is_array($keys)) {
    $keys = array(':id' => $keys);
  }
  $path = strtr('users/:id.:format', $keys);
  $path = preg_replace('/[\/\.\?]:format/', '', $path);
  return new ActionController_Path('GET', $path);
}

function edit_user_path($keys=array())
{
  if (!is_array($keys)) {
    $keys = array(':id' => $keys);
  }
  $path = strtr('users/:id/edit.:format', $keys);
  $path = preg_replace('/[\/\.\?]:format/', '', $path);
  return new ActionController_Path('GET', $path);
}

function create_user_path($keys=array())
{
  if (!is_array($keys)) {
    $keys = array(':id' => $keys);
  }
  $path = strtr('users.:format', $keys);
  $path = preg_replace('/[\/\.\?]:format/', '', $path);
  return new ActionController_Path('POST', $path);
}

function update_user_path($keys=array())
{
  if (!is_array($keys)) {
    $keys = array(':id' => $keys);
  }
  $path = strtr('users/:id.:format', $keys);
  $path = preg_replace('/[\/\.\?]:format/', '', $path);
  return new ActionController_Path('PUT', $path);
}

function delete_user_path($keys=array())
{
  if (!is_array($keys)) {
    $keys = array(':id' => $keys);
  }
  $path = strtr('users/:id.:format', $keys);
  $path = preg_replace('/[\/\.\?]:format/', '', $path);
  return new ActionController_Path('DELETE', $path);
} ?>