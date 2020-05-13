DELETE FROM mb_user WHERE is_active = False and cast(extract(epoch from now()) as integer) > timestamp_delete + 86400
