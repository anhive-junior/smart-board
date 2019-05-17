#!/bin/sh
### BEGIN INIT INFO
# Provides:          timeworks
# Required-Start:    $local_fs $syslog
# Required-Stop:     $local_fs $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# X-Interactive:     true
# Short-Description: Start/stop timeworks to control schedules
### END INIT INFO


PATH=/sbin:/usr/sbin:/bin:/usr/bin
PIDF=/run/timeworks.pid
PID=$(cat $PIDF)

do_start () {
        # Start remote input for application 
        /etc/hive/timeworks
}

do_status () {
        ps -ef | grep $PID
        return $PID
}

do_stop () {
        # check current status which is good or bat
        kill -9 $(ps -ef | grep $PID | awk '{print $2}')
		rm $PIDF
}

case "$1" in
  start|"")
        do_start
        exit $?
        ;;
  restart|reload|force-reload)
        do_stop
        do_start
        exit $?
        ;;
  stop)
        do_stop
        exit $?
        ;;
  status)
        do_status
        exit $?
        ;;
  *)
        echo "Usage: timeworks [start|stop|status]" >&2
        exit 3
        ;;
esac

:
