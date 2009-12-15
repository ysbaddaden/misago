<?php

# :namespace: Misago\ActionView\Helpers\DateHelper
function distance_of_time_in_words($from_time, $to_time=0)
{
  if (!$from_time instanceof Misago\ActiveSupport\Datetime) {
    $from_time = new Misago\ActiveSupport\Datetime($from_time);
  }
  if (!$to_time   instanceof Misago\ActiveSupport\Datetime) {
    $to_time = new Misago\ActiveSupport\Datetime($to_time);
  }
  
  $distance = round(abs($from_time->distance($to_time)) / 60.0);
  
  if ($distance < 1) {
    return t('less_than_x_minutes', array('context' => 'number.datetime.distance_in_words', 'count' => 1));
  }
  elseif ($distance < 2) {
    return t('x_minutes', array('context' => 'number.datetime.distance_in_words', 'count' => 1));
  }
  elseif ($distance < 44) {
    return t('x_minutes', array('context' => 'number.datetime.distance_in_words', 'count' => $distance));
  }
  elseif ($distance < 89) {
    return t('about_x_hours', array('context' => 'number.datetime.distance_in_words', 'count' => 1));
  }
  elseif ($distance < 1440) {
    return t('x_hours', array('context' => 'number.datetime.distance_in_words', 'count' => round($distance / 60)));
  }
  elseif ($distance < 2880) {
    return t('about_x_days', array('context' => 'number.datetime.distance_in_words', 'count' => 1));
  }
  elseif ($distance < 43200) {
    return t('x_days', array('context' => 'number.datetime.distance_in_words', 'count' => round($distance / 1440)));
  }
  elseif ($distance < 86400) {
    return t('about_x_months', array('context' => 'number.datetime.distance_in_words', 'count' => 1));
  }
  elseif ($distance < 525600) {
    return t('x_months', array('context' => 'number.datetime.distance_in_words', 'count' => round($distance / 43200)));
  }
  elseif ($distance < 1051199) {
    return t('about_x_years', array('context' => 'number.datetime.distance_in_words', 'count' => 1));
  }
  else {
    return t('over_x_years', array('context' => 'number.datetime.distance_in_words', 'count' => round($distance / 525600)));
  }
}

# Same as <tt>distance_of_time_in_words</tt> but with +$to_time+ fixed to now.
# :namespace: Misago\ActionView\Helpers\DateHelper
function distance_of_time_in_words_to_now($from_time) {
  return distance_of_time_in_words_to_now($from_time, 'now');
}

# Alias of <tt>distance_of_time_in_words_to_now</tt>.
# :namespace: Misago\ActionView\Helpers\DateHelper
function time_ago_in_words($from_time) {
  return distance_of_time_in_words_to_now($from_time);
}

?>
