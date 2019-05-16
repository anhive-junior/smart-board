#!/bin/bash
SERVICE_PATH=/var/www
getpid_amount=$(pgrep feh | wc -l)

feh -p -Y -x -q -D 5 -B black -F --zoom fill -R 3 -C $SERVICE_PATH/signage -e NanumGothic.woff/64 -K captions/ -r $SERVICE_PATH/media/playlist -nSmtime &

if [ $getpid_amount -gt 1 ]; then
	sleep 3
	if [ $! -eq $(pgrep feh | head -1) ]; then
		kill -9 $(pgrep feh | tail -1)
	else
		kill -9 $(pgrep feh | head -1)
	fi
fi
