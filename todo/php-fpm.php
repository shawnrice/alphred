<?php

$alphred_data = $_SERVER['HOME'] . '/Library/Application Support/Alfred 2/Workflows/com.alphred';

if ( ! file_exists( $alphred_data ) )
    mkdir( $_SERVER['HOME'] . '/Library/Application Support/Alfred 2/Workflows/com.alphred', 0755);

if ( ! file_exists( "{$alphred_data}/configs" ) )
    mkdir( "{$alphred_data}/configs" );

if ( ! file_exists( "{$alphred_data/configs/php.ini" ) ) {
    // Create a php.ini file
}

if ( ! file_exists( "{$alphred_data/configs/php-fpm.conf" ) ) {
    // Make a php-fpm.conf file

}

if ( ! file_exists( "{$alphred_data/scripts" ) ) {
    mkdir( "{$alphred_data/scripts", 0755 );
}

// Here are the start/stop controlers... maybe.

// So, what we'll do is to start a PHP-FPM at "/" and then we can use the full path (maybe)?
// Then, we'll use a common port, and then we'll also have a common "shut-down" script...



 php-fpm -c path|file  (ini)

   --fpm-config file (php-fpm.conf)
pm = ondemand
pm.start_servers = 0



   pm = ondemand
pm.max_children = 5
pm.process_idle_timeout = 10s
pm.max_requests = 200





[global]
pid = /var/run/php-fpm/pool1.pid
log_level = notice
emergency_restart_threshold = 0
emergency_restart_interval = 0
process_control_timeout = 0
daemonize = yes

[pool1]
listen = /var/run/php-fpm/pool1.sock
listen.owner = pool1
listen.group = pool1
listen.mode = 0666

user = pool1
group = pool1

pm = ondemand
pm.max_children = 5
pm.process_idle_timeout = 10s
pm.max_requests = 500



#! /bin/sh
#
# chkconfig: - 84 16
# description:  PHP FastCGI Process Manager for pool 'pool1'
# processname: php-fpm-pool1
# config: /etc/php-fpm.d/pool1.conf
# pidfile: /var/run/php-fpm/pool1.pid

# Standard LSB functions
#. /lib/lsb/init-functions

# Source function library.
. /etc/init.d/functions

# Check that networking is up.
. /etc/sysconfig/network

if [ "$NETWORKING" = "no" ]
then
    exit 0
fi

RETVAL=0
prog="php-fpm-pool1"
pidfile=/var/run/php-fpm/pool1.pid
lockfile=/var/lock/subsys/php-fpm-pool1
fpmconfig=/etc/php-fpm.d/pool1

start () {
    echo -n $"Starting $prog: "
    daemon --pidfile ${pidfile} php-fpm --fpm-config=${fpmconfig} --daemonize
    RETVAL=$?
    echo
    [ $RETVAL -eq 0 ] && touch ${lockfile}
}
stop () {
    echo -n $"Stopping $prog: "
    killproc -p ${pidfile} php-fpm
    RETVAL=$?
    echo
    if [ $RETVAL -eq 0 ] ; then
        rm -f ${lockfile} ${pidfile}
    fi
}

restart () {
        stop
        start
}

reload () {
    echo -n $"Reloading $prog: "
    killproc -p ${pidfile} php-fpm -USR2
    RETVAL=$?
    echo
}


# See how we were called.
case "$1" in
  start)
    start
    ;;
  stop)
    stop
    ;;
  status)
    status -p ${pidfile} php-fpm
    RETVAL=$?
    ;;
  restart)
    restart
    ;;
  reload|force-reload)
    reload
    ;;
  condrestart|try-restart)
    [ -f ${lockfile} ] && restart || :
    ;;
  *)
    echo $"Usage: $0 {start|stop|status|restart|reload|force-reload|condrestart|try-restart}"
    RETVAL=2
        ;;
esac

exit $RETVAL