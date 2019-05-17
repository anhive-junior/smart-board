#!/bin/bash
#
#       /etc/rc.d/init.d/timeworks
#
#       timeworks    Hive Task Scheduler 1.0
#
# chkconfig: 345 70 30
# description: timeworks is a Hive Task Scheduler with Web Management
# processname: timeworks

# Source function library.
. /etc/init.d/functions

RETVAL=0
prog="timeworks"
LOCKFILE=/var/lock/subsys/$prog

EXEC=/etc/hive/timeworks
PIDF=/run/timeworks.pid
PID=$(cat $PIDF)
USER=root
DOMAIN=hive

start() {
        echo -n "Starting $prog: "
        daemon --user $USER $EXEC start-domain $DOMAIN
        RETVAL=$?
        [ $RETVAL -eq 0 ] && touch $LOCKFILE
        echo
        return $RETVAL
}

stop() {
        echo -n "Shutting down $prog: "
		kill -9 $(ps -ef | grep $PID | awk '{print $2}')
        $EXEC stop-domain $DOMAIN && success || failure
        RETVAL=$?
        [ $RETVAL -eq 0 ] && rm -f $LOCKFILE
        echo
        return $RETVAL
}

status() {
        echo -n "Checking $prog status: "
        $EXEC list-domains | grep $DOMAIN
        RETVAL=$?
        return $RETVAL
}


case "$1" in
    start)
        start
        ;;
    stop)
        stop
        ;;
    status)
        status
        ;;
    restart)
        stop
        start
        ;;
    *)
        echo "Usage: $prog {start|stop|status|restart}"
        exit 1
        ;;
esac
exit $RETVAL

