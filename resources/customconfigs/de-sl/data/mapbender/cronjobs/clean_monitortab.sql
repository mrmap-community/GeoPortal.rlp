DELETE FROM mapbender.mb_monitor WHERE timestamp_end < extract(epoch from current_date - integer '60');
