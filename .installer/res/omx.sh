#!/bin/bash

SERVICE='omxplayer'

if ps ax | grep -v grep | grep $SERVICE > /dev/null
then
echo "running" # >> /dev/null
else
#omxplayer -o hdmi /path/to/your/video/file/video.mp4 &
/usr/bin/lxterminal -e /usr/bin/omxplayer -b -o hdmi --loop /home/pi/media/video/.omx_default.* &

fi
