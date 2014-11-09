<?php

// This doesn't work right now. I need to figure out how to get information directly from php-fpm

    private function fpm_conf() {
        $php_fpm_tmp_dir = $this->base_path . "/php";
        $php_fpm_run_dir = dirname( $_SERVER['PWD'] );
        if ( ! file_exists( $php_fpm_tmp_dir ) ) { mkdir( $php_fpm_tmp_dir, 0755 ); }
// user = ' . $_SERVER['USER'] . '
// group = ' . $_SERVER['USER'] . '
        return '[global]
pid = ' . $this->base_path . '/php-fpm.pid
error_log = /usr/share/php/var/log/php-fpm.log
log_level = warning
emergency_restart_threshold = 25
emergency_restart_interval = 5m
process_control_timeout = 5m
[www]
listen = 127.0.0.1:' . $this->port . '
listen.allowed_clients = 127.0.0.1
pm = ondemand
pm.max_children = 5
pm.start_servers = 2
pm.process_idle_timeout = 10s;
pm.max_requests = 100
ping.path = /ping
ping.response = pong
request_terminate_timeout = 5m
php_admin_value[session.save_path] = ' . $php_fpm_tmp_dir . '/session';
// chroot = ' . $php_fpm_run_dir . '
// ';
    }

    private function php_ini() {
        return '[PHP]
engine = On
short_open_tag = Off
asp_tags = Off
precision = 14
output_buffering = 4096
zlib.output_compression = Off
implicit_flush = Off
serialize_precision = 17
zend.enable_gc = On
expose_php = On
max_execution_time = 30
max_input_time = 60
memory_limit = 128M
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_erors = Off
display_startup_errors = Off
log_errors = Off
log_errors_max_len = 1024
ignore_repeated_errors = Off
ignore_repeated_source = Off
report_memleaks = On
date.timezone = ' . exec( 'tz=`ls -l /etc/localtime` && echo ${tz#*/zoneinfo/}' ) . '
track_errors = Off
html_errors = On
variables_order = "GPCS"
request_order = "GP"
register_argc_argv = Off
auto_globals_jit = On
post_max_size = 8M
default_mimetype = "text/html"
enable_dl = Off
file_uploads = Off
upload_max_filesize = 2M
max_file_uploads = 20
allow_url_fopen = On
allow_url_include = Off
default_socket_timeout = 60
[CLI Server]
cli_server.color = On
[SQL]
sql.safe_mode = Off
[bcmath]
bcmath.scale = 0
[Session]
session.save_handler = files
session.use_strict_mode = 0
session.use_cookies = 1
session.use_only_cookies = 1
session.name = PHPSESSID
session.auto_start = 0
session.cookie_lifetime = 0
session.cookie_path = /
session.cookie_domain =
session.cookie_httponly =
session.serialize_handler = php
session.gc_probability = 1
session.gc_divisor = 1000
session.gc_maxlifetime = 1440
session.referer_check =
session.cache_limiter = nocache
session.cache_expire = 180
session.use_trans_sid = 0
session.hash_function = 0
session.hash_bits_per_character = 5
url_rewriter.tags = "a=href,area=href,frame=src,input=src,form=fakeentry"
        ';
    }

       private function get_bin_path() {
        $path = trim( exec( 'which php-fpm' ) );
        return ( ! empty( $path ) ) ? $path : false;
    }
$cmd = "lsof -n -i4TCP:{$this->port} | grep LISTEN";
        return ( exec( $cmd ) ) ? true : false; // return true if there is something running and false otherwise
        $bin = $this->get_bin_path();
        if ( ! $bin ) return false; // or throw an exception
        $cmd = "$bin  -c '{$this->base_path}/php.ini' --fpm-config '{$this->base_path}/php-fpm.conf'";

        // if ( ! file_exists( $this->base_path . "/php-fpm.conf" ) ) {
            file_put_contents( $this->base_path . "/php-fpm.conf", $this->fpm_conf() );
        // }
        // if ( ! file_exists( $this->base_path . "/php.ini" ) ) {
            file_put_contents( $this->base_path . "/php.ini", $this->php_ini() );
        // }