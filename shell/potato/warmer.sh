#!/bin/sh
WARMER_SCRIPT=warmer.php
QUEUE_SCRIPT=queue.php
BASEDIR=`echo $0 | sed 's/warmer\.sh//g'`
PHP_BIN=`which php`

if ! ps auxwww | grep "$BASEDIR$WARMER_SCRIPT" | grep -v grep 1>/dev/null 2>/dev/null
then $PHP_BIN $BASEDIR$WARMER_SCRIPT &
fi ;
if ! ps auxwww | grep "$BASEDIR$QUEUE_SCRIPT" | grep -v grep 1>/dev/null 2>/dev/null
then $PHP_BIN $BASEDIR$QUEUE_SCRIPT &
fi ;