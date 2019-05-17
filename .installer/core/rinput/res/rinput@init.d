#!/bin/sh
### BEGIN INIT INFO
# Provides:          rinput
# Required-Start:    $local_fs $network $syslog
# Required-Stop:     $local_fs $network $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# X-Interactive:     true
# Short-Description: Start/stop rinput deamon service
### END INIT INFO


PATH=/sbin:/usr/sbin:/bin:/usr/bin
PIDF=/run/rinput.pid
PID=$(cat $PIDF)

do_start () {
        # Start remote input for application 
        /etc/hive/rinputd
}

do_status () {
        ps -ef | grep $PID
        return $PID
}

do_stop () {
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
        echo "Usage: rinput [start|stop|status]" >&2
        exit 3
        ;;
esac

:
