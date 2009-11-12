<?php

# :namespace: ActiveSupport\Helper\DateHelper
function distance_of_time_in_words($from_time, $to_time=0)
{
  if (!$from_time instanceof ActiveSupport_Datetime) $from_time = new ActiveSupport_Datetime($from_time);
  if (!$to_time   instanceof ActiveSupport_Datetime) $to_time   = new ActiveSupport_Datetime($to_time);
  
  $distance = round(abs($from_time->distance($to_time)) / 60);
  
  if ($distance < 2) {
    return t('less_than_x_minutes', array('scope' => 'number.datetime.distance_in_words', 'count' => 1));
  }
  elseif ($distance < 44) {
    return t('x_minutes', array('scope' => 'number.datetime.distance_in_words', 'count' => $distance));
  }
  elseif ($distance < 89) {
    return t('about_x_hours', array('scope' => 'number.datetime.distance_in_words', 'count' => 1));
  }
  elseif ($distance / 3600 < 24) {
    return t('about_x_hours', array('scope' => 'number.datetime.distance_in_words', 'count' => round($distance / 3600)));
  }
  elseif ($distance / 3600 / 24 < 2) {
    return t('x_days', array('scope' => 'number.datetime.distance_in_words', 'count' => 1));
  }
  elseif ($distance / 3600 / 24 < 30) {
    return t('x_days', array('scope' => 'number.datetime.distance_in_words', 'count' => round($distance / 3600 / 24)));
  }
  elseif ($distance / 3600 / 24 / 30 < 2) {
    return t('about_x_months', array('scope' => 'number.datetime.distance_in_words', 'count' => 1));
  }
  elseif ($distance / 3600 / 24 / 30 < 12) {
    return t('about_x_months', array('scope' => 'number.datetime.distance_in_words', 'count' => round($distance / 3600 / 24 / 30)));
  }
  elseif ($distance / 3600 / 24 / 30 / 12 < 2) {
    return t('x_years', array('scope' => 'number.datetime.distance_in_words', 'count' => 1));
  }
  else {
    return t('over_x_months', array('scope' => 'number.datetime.distance_in_words', 'count' => round($distance / 3600 / 24 / 30 / 12)));
  }
}

# Same as of <tt>distance_of_time_in_words</tt> with +to_time+ fixed to now.
# :namespace: ActiveSupport\Helper\DateHelper
function distance_of_time_in_words_to_now($from_time)
{
  return distance_of_time_in_words_to_now($from_time, 'now');
}


# Alias of <tt>distance_of_time_in_words_to_now</tt>.
# :namespace: ActiveSupport\Helper\DateHelper
function time_ago_in_words($from_time)
{
  return distance_of_time_in_words_to_now($from_time);
}

?>
